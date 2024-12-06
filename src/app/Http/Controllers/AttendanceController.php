<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $workingStatus = null;

        $attendance = Attendance::todayRecord($user->id)->first(); // 今日の勤怠レコードを取得

        if (!$attendance) {
            $workingStatus = 1; // 出勤前
        } else {
            // attendance_status_idの値に基づいてworkingStatusを設定
            if ($attendance->attendance_status_id == 2) {
                $workingStatus = 2; // 勤務中
            } elseif ($attendance->attendance_status_id == 3) {
                $workingStatus = 3; // 休憩中
            } elseif ($attendance->attendance_status_id == 4) {
                $workingStatus = 4; // 退勤済み
            }
        }
        return view('attendance', compact('user', 'workingStatus'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($request->input('start_work')) {
            Attendance::startWork($user->id);
        } elseif ($request->input('end_work')) {
            Attendance::endWork($user->id);
        } elseif ($request->input('start_break')) {
            Attendance::startBreak($user->id);
        } elseif ($request->input('end_break')) {
            Attendance::endBreak($user->id);
        }

        return redirect()->route('attendance.show');
    }
}
