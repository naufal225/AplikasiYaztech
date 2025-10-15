<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('feature_settings')->insert([
            [
                'nama_fitur' => 'cuti',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_fitur' => 'reimbursement',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_fitur' => 'overtime',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_fitur' => 'perjalanan_dinas',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
