<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingBbns = DB::table('costing_bbn_masters')->get();
        $usedCodes = [];
        $existingNames = [];

        foreach ($existingBbns as $b) {
            if ($b->kode) {
                $usedCodes[strtoupper(trim($b->kode))] = true;
            }
            $cleanName = strtolower(trim($b->name ?: $b->display_name ?: ''));
            if ($cleanName) {
                $existingNames[$cleanName] = $b->id;
            }
        }

        $regions = DB::table('costing_regions')->where('is_active', true)->orderBy('id')->get();
        $now = now();
        $toInsert = [];

        foreach ($regions as $r) {
            $areaClean = strtolower(trim($r->area));

            // If area already exists in costing_bbn_masters, make sure its pulau and provinsi are up-to-date
            if (isset($existingNames[$areaClean])) {
                DB::table('costing_bbn_masters')
                    ->where('id', $existingNames[$areaClean])
                    ->update([
                        'pulau'      => $r->pulau,
                        'provinsi'   => $r->provinsi,
                        'updated_at' => $now,
                    ]);
                continue;
            }

            // Generate a unique 3-4 letter uppercase code
            $code = $this->generateUniqueCode($r->area, $usedCodes);
            $usedCodes[$code] = true;
            $existingNames[$areaClean] = true;

            $toInsert[] = [
                'odoo_bbn_id'   => null,
                'kode'          => $code,
                'name'          => $r->area,
                'display_name'  => $r->area,
                'pulau'         => $r->pulau,
                'provinsi'      => $r->provinsi,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];
        }

        if (!empty($toInsert)) {
            foreach (array_chunk($toInsert, 50) as $chunk) {
                DB::table('costing_bbn_masters')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('costing_bbn_masters')->whereNull('odoo_bbn_id')->delete();
    }

    private function generateUniqueCode(string $name, array $usedCodes): string
    {
        $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name));
        if (strlen($clean) < 3) {
            $clean = str_pad($clean, 3, 'X');
        }

        // Strategy 1: first letter + consonants (e.g. KDS for Kudus, SLT for Salatiga)
        $first = $clean[0];
        $rest = substr($clean, 1);
        $consonants = preg_replace('/[AEIOU]/', '', $rest);
        if (strlen($consonants) >= 2) {
            $candidate = $first . substr($consonants, 0, 2);
            if (!isset($usedCodes[$candidate])) {
                return $candidate;
            }
        }

        // Strategy 2: first 3 characters
        $candidate = substr($clean, 0, 3);
        if (!isset($usedCodes[$candidate])) {
            return $candidate;
        }

        // Strategy 3: first 4 characters
        if (strlen($clean) >= 4) {
            $candidate = substr($clean, 0, 4);
            if (!isset($usedCodes[$candidate])) {
                return $candidate;
            }
        }

        // Strategy 4: prefix + incremental number
        for ($i = 1; $i <= 99; $i++) {
            $suffix = (string)$i;
            $candidate = substr($clean, 0, 4 - strlen($suffix)) . $suffix;
            if (!isset($usedCodes[$candidate])) {
                return $candidate;
            }
        }

        return substr(strtoupper(md5($name)), 0, 4);
    }
};
