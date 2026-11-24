<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BackupSchedule;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller implements HasMiddleware
{
    protected BackupService $backupService;

    public static function middleware(): array
    {
        return [
            new Middleware(static function ($request, $next) {
                if (!auth()->user()?->isAdmin()) {
                    abort(403, 'Admin access required.');
                }
                return $next($request);
            }),
        ];
    }

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    public function index()
    {
        $backups = $this->backupService->list();
        $schedule = BackupSchedule::first() ?? new BackupSchedule([
            'enabled' => true,
            'frequency' => 'daily',
            'time' => '02:00',
            'prune_enabled' => true,
            'keep_daily' => 7,
            'keep_weekly' => 4,
            'keep_monthly' => 6,
        ]);

        return view('admin.backups.index', compact('backups', 'schedule'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'remark' => 'nullable|string|max:255',
        ]);

        try {
            $filename = $this->backupService->create($request->input('remark'));
            return redirect()->route('admin.backups.index')->with('success', "Backup {$filename} berhasil dibuat!");
        } catch (\Throwable $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            return redirect()->route('admin.backups.index')->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function updateSchedule(Request $request)
    {
        $request->validate([
            'enabled'       => 'nullable|boolean',
            'frequency'     => 'required|in:daily,weekly,monthly',
            'time'          => 'required|date_format:H:i',
            'prune_enabled' => 'nullable|boolean',
            'keep_daily'    => 'nullable|integer|min:1|max:365',
            'keep_weekly'   => 'nullable|integer|min:1|max:52',
            'keep_monthly'  => 'nullable|integer|min:1|max:24',
        ]);

        BackupSchedule::updateOrCreate(
            ['id' => 1],
            [
                'enabled'       => $request->boolean('enabled'),
                'frequency'     => $request->input('frequency'),
                'time'          => $request->input('time'),
                'prune_enabled' => $request->boolean('prune_enabled'),
                'keep_daily'    => $request->input('keep_daily', 7),
                'keep_weekly'   => $request->input('keep_weekly', 4),
                'keep_monthly'  => $request->input('keep_monthly', 6),
            ]
        );

        return redirect()->route('admin.backups.index')->with('success', 'Pengaturan jadwal backup berhasil disimpan!');
    }

    public function download(string $filename)
    {
        return $this->backupService->download($filename);
    }

    public function delete(string $filename)
    {
        try {
            $this->backupService->delete($filename);
            return redirect()->route('admin.backups.index')->with('success', "Backup {$filename} berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }

    public function prune()
    {
        $schedule = BackupSchedule::first();

        try {
            $result = $this->backupService->prune(
                $schedule->keep_daily ?? 7,
                $schedule->keep_weekly ?? 4,
                $schedule->keep_monthly ?? 6
            );
            return redirect()->route('admin.backups.index')
                ->with('success', "Pruning selesai: Menahan {$result['kept']} backup, menghapus {$result['deleted']} backup usang.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')->with('error', 'Gagal membersihkan backup: ' . $e->getMessage());
        }
    }
}
