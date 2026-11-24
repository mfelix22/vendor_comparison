<?php

namespace App\Services;

use App\Models\BackupLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupService
{
    protected string $disk = 'local';
    protected string $backupFolder = 'backups';

    /**
     * Create a backup of the MySQL database and compress it with gzip (.sql.gz).
     *
     * @param string|null $remark
     * @return string Filename of the generated backup
     * @throws \Exception
     */
    public function create(?string $remark = null): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $filename = "backup-{$timestamp}.sql.gz";

        $backupDir = Storage::disk($this->disk)->path($this->backupFolder);
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Try mysqldump first if available, otherwise fall back to pure PHP PDO dumper
        $dumpSuccess = false;
        if ($this->isMysqldumpAvailable()) {
            try {
                $dumpSuccess = $this->dumpWithMysqldump($backupPath);
            } catch (\Throwable $e) {
                Log::warning('mysqldump failed, falling back to PDO dumper: ' . $e->getMessage());
                $dumpSuccess = false;
            }
        }

        if (!$dumpSuccess) {
            $this->dumpWithPdo($backupPath);
        }

        if (!file_exists($backupPath) || filesize($backupPath) === 0) {
            throw new \Exception('Failed to create backup file or file is empty.');
        }

        $fileSize = filesize($backupPath);

        $creator = 'System/Scheduler';
        if (Auth::check() && Auth::user()) {
            $creator = Auth::user()->name ?? 'User #' . Auth::id();
        }

        BackupLog::create([
            'filename' => $filename,
            'path' => $this->backupFolder . '/' . $filename,
            'disk' => $this->disk,
            'size' => $fileSize,
            'remark' => $remark,
            'created_by' => $creator,
        ]);

        return $filename;
    }

    /**
     * Get list of backups ordered by latest.
     */
    public function list()
    {
        return BackupLog::orderByDesc('created_at')->get();
    }

    /**
     * Download a backup file.
     */
    public function download(string $filename)
    {
        $path = $this->backupFolder . '/' . $filename;
        if (!Storage::disk($this->disk)->exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::disk($this->disk)->download($path, $filename, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * Delete a backup file and log.
     */
    public function delete(string $filename): bool
    {
        $path = $this->backupFolder . '/' . $filename;

        if (Storage::disk($this->disk)->exists($path)) {
            Storage::disk($this->disk)->delete($path);
        }

        BackupLog::where('filename', $filename)->delete();
        return true;
    }

    /**
     * Delete multiple backup files.
     */
    public function deleteBatch(array $filenames): int
    {
        $deleted = 0;
        foreach ($filenames as $filename) {
            try {
                $this->delete($filename);
                $deleted++;
            } catch (\Throwable $e) {
                Log::error("Failed to delete backup {$filename}: " . $e->getMessage());
            }
        }
        return $deleted;
    }

    /**
     * Prune old backups based on retention policy.
     */
    public function prune(int $keepDaily = 7, int $keepWeekly = 4, int $keepMonthly = 6): array
    {
        $backups = BackupLog::orderByDesc('created_at')->get();

        $keepSet = [];
        $dailyCounts = [];
        $weeklyCounts = [];
        $monthlyCounts = [];

        foreach ($backups as $backup) {
            $date = $backup->created_at;
            $dayKey = $date->format('Y-m-d');
            $weekKey = $date->format('Y-W');
            $monthKey = $date->format('Y-m');
            $keep = false;

            if (!isset($dailyCounts[$dayKey])) {
                $dailyCounts[$dayKey] = 0;
            }
            if ($dailyCounts[$dayKey] < 1 && count($dailyCounts) <= $keepDaily) {
                $keep = true;
                $dailyCounts[$dayKey]++;
            }

            if (!isset($weeklyCounts[$weekKey])) {
                $weeklyCounts[$weekKey] = 0;
            }
            if ($weeklyCounts[$weekKey] < 1 && count($weeklyCounts) <= $keepWeekly) {
                $keep = true;
                $weeklyCounts[$weekKey]++;
            }

            if (!isset($monthlyCounts[$monthKey])) {
                $monthlyCounts[$monthKey] = 0;
            }
            if ($monthlyCounts[$monthKey] < 1 && count($monthlyCounts) <= $keepMonthly) {
                $keep = true;
                $monthlyCounts[$monthKey]++;
            }

            if ($keep) {
                $keepSet[$backup->filename] = true;
            }
        }

        $deleted = [];
        foreach ($backups as $backup) {
            if (!isset($keepSet[$backup->filename])) {
                try {
                    $this->delete($backup->filename);
                    $deleted[] = $backup->filename;
                } catch (\Throwable $e) {
                    Log::error("Prune error on {$backup->filename}: " . $e->getMessage());
                }
            }
        }

        return ['kept' => count($keepSet), 'deleted' => count($deleted)];
    }

    /**
     * Check if mysqldump command is installed and executable in PATH.
     */
    protected function isMysqldumpAvailable(): bool
    {
        if (!function_exists('exec')) {
            return false;
        }

        $cmd = (DIRECTORY_SEPARATOR === '\\') ? 'where mysqldump' : 'which mysqldump';
        $output = [];
        $returnVar = 1;
        @exec($cmd . ' 2>&1', $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Dump database using mysqldump utility.
     */
    protected function dumpWithMysqldump(string $targetGzPath): bool
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $tempSqlPath = dirname($targetGzPath) . DIRECTORY_SEPARATOR . 'temp_' . uniqid() . '.sql';

        $escapedPass = escapeshellarg($password);
        $escapedUser = escapeshellarg($username);
        $escapedHost = escapeshellarg($host);
        $escapedPort = escapeshellarg((string)$port);
        $escapedDb   = escapeshellarg($database);
        $escapedTemp = escapeshellarg($tempSqlPath);

        // Run mysqldump with single-transaction (no lock on InnoDB)
        $command = "mysqldump --host={$escapedHost} --port={$escapedPort} --user={$escapedUser} --password={$escapedPass} --single-transaction --quick --routines --triggers --result-file={$escapedTemp} {$escapedDb} 2>&1";

        $output = [];
        $returnVar = 1;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0 || !file_exists($tempSqlPath) || filesize($tempSqlPath) === 0) {
            if (file_exists($tempSqlPath)) {
                @unlink($tempSqlPath);
            }
            return false;
        }

        // Compress temp SQL into .sql.gz
        $fp = fopen($tempSqlPath, 'rb');
        $gz = gzopen($targetGzPath, 'wb9');
        while (!feof($fp)) {
            gzwrite($gz, fread($fp, 65536));
        }
        fclose($fp);
        gzclose($gz);
        @unlink($tempSqlPath);

        return true;
    }

    /**
     * Dump database directly via PDO streaming into a gzip file.
     * Guaranteed to work in any PHP environment without external CLI binaries.
     */
    protected function dumpWithPdo(string $targetGzPath): void
    {
        $pdo = DB::connection('mysql')->getPdo();
        $dbName = config('database.connections.mysql.database');

        $gz = gzopen($targetGzPath, 'wb9');
        if (!$gz) {
            throw new \Exception("Cannot open gzip file for writing at {$targetGzPath}");
        }

        // Header
        $header = "-- Vendor Comparison MySQL Database Dump\n"
            . "-- Generated: " . Carbon::now()->toIso8601String() . "\n"
            . "-- Database: `{$dbName}`\n"
            . "-- ------------------------------------------------------\n\n"
            . "SET NAMES utf8mb4;\n"
            . "SET FOREIGN_KEY_CHECKS = 0;\n"
            . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
            . "SET AUTOCOMMIT = 0;\n"
            . "START TRANSACTION;\n\n";
        gzwrite($gz, $header);

        // Get all base tables
        $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        $tables = [];
        while ($row = $tablesStmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        foreach ($tables as $table) {
            // Write table drop and create schema
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? '';

            gzwrite($gz, "--\n-- Table structure for table `{$table}`\n--\n\n");
            gzwrite($gz, "DROP TABLE IF EXISTS `{$table}`;\n");
            gzwrite($gz, $createSql . ";\n\n");

            // Write table data
            gzwrite($gz, "--\n-- Dumping data for table `{$table}`\n--\n\n");
            
            $dataStmt = $pdo->prepare("SELECT * FROM `{$table}`");
            $dataStmt->execute();

            $batch = [];
            $batchSize = 200;

            while ($row = $dataStmt->fetch(\PDO::FETCH_ASSOC)) {
                $values = [];
                foreach ($row as $val) {
                    if (is_null($val)) {
                        $values[] = 'NULL';
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $values[] = $val;
                    } else {
                        $values[] = $pdo->quote((string)$val);
                    }
                }
                $batch[] = '(' . implode(', ', $values) . ')';

                if (count($batch) >= $batchSize) {
                    gzwrite($gz, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                gzwrite($gz, "INSERT INTO `{$table}` VALUES\n" . implode(",\n", $batch) . ";\n");
            }

            gzwrite($gz, "\n");
        }

        // Footer
        $footer = "COMMIT;\n"
            . "SET FOREIGN_KEY_CHECKS = 1;\n"
            . "-- Dump completed on " . Carbon::now()->toIso8601String() . "\n";
        gzwrite($gz, $footer);

        gzclose($gz);
    }
}
