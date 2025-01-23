<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $breakTimes = [];

        // 12/1から1/15までの日付を生成
        $startDate = Carbon::create(2024, 12, 1);
        $endDate = Carbon::create(2025, 1, 15);
        $date = $startDate->copy();

        while ($date->lte($endDate)) {
            $currentDate = $date->toDateString();

            // user_id 2 のデータ
            $attendanceId = Attendance::where('user_id', 2)
                ->where('date', $currentDate)
                ->value('id');

            $startTime = $date->copy()->setTime(12, rand(0, 59));
            $endTime = $startTime->copy()->addMinutes(rand(0, 59));
            $breakTime = $startTime->diffInMinutes($endTime);

            $breakTimes[] = [
                'attendance_id' => $attendanceId,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'break_time' => $breakTime,
                'created_at' => $startTime,
                'updated_at' => $endTime,
            ];

            // user_id 3 のデータ
            $attendanceId = Attendance::where('user_id', 3)
                ->where('date', $currentDate)
                ->value('id');

            $startTime = $date->copy()->setTime(12, rand(0, 59));
            $endTime = $startTime->copy()->addMinutes(rand(0, 59));
            $breakTime = $startTime->diffInMinutes($endTime);

            $breakTimes[] = [
                'attendance_id' => $attendanceId,
                'start_time' => $startTime->toDateTimeString(),
                'end_time' => $endTime->toDateTimeString(),
                'break_time' => $breakTime,
                'created_at' => $startTime,
                'updated_at' => $endTime,
            ];

            $date->addDay();
        }

        DB::table('break_times')->insert($breakTimes);
    }
}
