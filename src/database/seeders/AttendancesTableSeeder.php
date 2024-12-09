<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = [
            [
                'id' => 1,
                'user_id' => 1,
                'date' => '2024-11-01',
                'start_time' => '2024-11-01 09:43:22',
                'end_time' => '2024-11-01 18:45:25',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'date' => '2024-11-02',
                'start_time' => '2024-11-02 07:43:22',
                'end_time' => '2024-11-02 17:45:25',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'date' => '2024-11-03',
                'start_time' => '2024-11-03 08:59:35',
                'end_time' => '2024-11-03 18:00:21',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'user_id' => 1,
                'date' => '2024-11-30',
                'start_time' => '2024-11-30 08:57:11',
                'end_time' => '2024-11-30 18:11:28',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'user_id' => 1,
                'date' => '2025-01-01',
                'start_time' => '2025-01-01 09:43:22',
                'end_time' => '2025-01-01 18:45:25',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'user_id' => 1,
                'date' => '2025-01-02',
                'start_time' => '2025-01-02 07:43:22',
                'end_time' => '2025-01-02 17:45:25',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'user_id' => 1,
                'date' => '2025-01-03',
                'start_time' => '2025-01-03 08:59:35',
                'end_time' => '2025-01-03 18:00:21',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'user_id' => 1,
                'date' => '2025-01-31',
                'start_time' => '2025-01-31 08:57:11',
                'end_time' => '2025-01-31 18:11:28',
                'attendance_status_id' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('attendances')->insert($attendances);
    }
}
