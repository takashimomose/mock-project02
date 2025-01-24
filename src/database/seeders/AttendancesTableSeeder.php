<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = [];

        // 12/1から1/15までの日付を生成
        $startDate = Carbon::create(2024, 12, 1);
        $endDate = Carbon::create(2025, 1, 15);
        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            // user_id 2 のデータ
            $startTime = $date->copy()->setTime(9, rand(0, 59));
            $endTime = $startTime->copy()->addHours(8)->addMinutes(rand(0, 30));
            $workingHours = $startTime->diffInMinutes($endTime);

            $attendances[] = [
                'user_id' => 2,
                'date' => $date->toDateString(),
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'working_hours' => $workingHours,
                'attendance_status_id' => 4,
                'created_at' => $startTime,
                'updated_at' => $endTime,
            ];

            // user_id 3 のデータ
            $startTime = $date->copy()->setTime(10, rand(0, 59));
            $endTime = $startTime->copy()->addHours(7)->addMinutes(rand(0, 30));
            $workingHours = $startTime->diffInMinutes($endTime);

            $attendances[] = [
                'user_id' => 3,
                'date' => $date->toDateString(),
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'working_hours' => $workingHours,
                'attendance_status_id' => 4,
                'created_at' => $startTime,
                'updated_at' => $endTime,
            ];

            $date->addDay();
        }

        DB::table('attendances')->insert($attendances);
    }
}
