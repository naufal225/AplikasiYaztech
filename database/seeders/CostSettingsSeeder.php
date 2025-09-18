<?php

namespace Database\Seeders;

use App\Models\CostSetting;
use Illuminate\Database\Seeder;

class CostSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            [
                'key' => 'OVERTIME_COSTS',
                'name' => 'Overtime Costs',
                'description' => 'Biaya lembur per jam',
                'value' => 25000
            ],
            [
                'key' => 'OVERTIME_BONUS_COSTS',
                'name' => 'Overtime Bonus Costs',
                'description' => 'Biaya bonus lembur per jam',
                'value' => 30000
            ],
            [
                'key' => 'TRAVEL_COSTS_PER_DAY',
                'name' => 'Travel Costs Per Day',
                'description' => 'Biaya perjalanan dinas per hari',
                'value' => 125000
            ],
            [
                'key' => 'TRAVEL_COSTS_WEEK_DAY',
                'name' => 'Travel Costs Week Day',
                'description' => 'Biaya perjalanan dinas hari kerja',
                'value' => 150000
            ],
            [
                'key' => 'TRAVEL_COSTS_WEEK_END',
                'name' => 'Travel Costs Week End',
                'description' => 'Biaya perjalanan dinas akhir pekan',
                'value' => 225000
            ]
        ];

        foreach ($settings as $setting) {
            CostSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
