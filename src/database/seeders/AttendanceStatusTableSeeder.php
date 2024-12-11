<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendanceStatus = [
            [
                'status_name' => '勤務外',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_name' => '出勤中',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_name' => '休憩中',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'status_name' => '退勤済',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('attendance_status')->insert($attendanceStatus);
    }
}
