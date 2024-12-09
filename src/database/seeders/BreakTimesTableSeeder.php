<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $breakTimes = [
            [
                'attendance_id' => 1,
                'start_time' => '2024-11-01 10:43:22',
                'end_time' => '2024-11-01 10:45:22',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 2,
                'start_time' => '2024-11-02 11:43:22',
                'end_time' => '2024-11-02 11:50:25',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 3,
                'start_time' => '2024-11-03 14:59:35',
                'end_time' => '2024-11-03 15:00:21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 5,
                'start_time' => '2025-01-01 10:43:22',
                'end_time' => '2025-01-01 10:45:22',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 6,
                'start_time' => '2025-01-02 11:43:22',
                'end_time' => '2025-01-02 11:50:25',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'attendance_id' => 7,
                'start_time' => '2025-01-03 14:59:35',
                'end_time' => '2025-01-03 15:00:21',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('break_times')->insert($breakTimes);
    }
}
