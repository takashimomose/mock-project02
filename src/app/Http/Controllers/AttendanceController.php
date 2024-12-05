<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $workingStatus = null;

        $attendance = Attendance::todayRecord($user->id)->first(); // 今日の出勤レコードを取得

        if (!$attendance) {
            $workingStatus = 1; // 出勤前
        } else {
            $latestBreak = $attendance->breakTimes()->latest()->first();

            if (!$latestBreak || ($latestBreak->start_time === null && $attendance->end_time === null)) {
                $workingStatus = 2; // 勤務中
            } elseif ($latestBreak->end_time === null) {
                $workingStatus = 3; // 休憩中
            } elseif ($attendance->end_time !== null) {
                $workingStatus = 4; // 退勤済み
            }
        }

        return view('attendance', compact('user', 'workingStatus'));
    }
}
