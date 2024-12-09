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

        $attendance = Attendance::getTodayRecord($user->id); // 今日の勤怠レコードを取得

        if (!$attendance) {
            $workingStatus = Attendance::STATUS_BEFORE;
        } else {
            switch ($attendance->attendance_status_id) {
                case Attendance::STATUS_WORKING:
                    $workingStatus = Attendance::STATUS_WORKING;
                    break;

                case Attendance::STATUS_BREAK:
                    $workingStatus = Attendance::STATUS_BREAK;
                    break;
                    
                case Attendance::STATUS_FINISHED:
                    $workingStatus = Attendance::STATUS_FINISHED;
                    break;
            }
        }

        return view('attendance', compact('user', 'workingStatus'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        switch (true) {
            case ($request->input('start_work') == Attendance::STATUS_WORKING):
                Attendance::startWork($user->id);
                break;

            case ($request->input('end_work') == Attendance::STATUS_FINISHED):
                Attendance::endWork($user->id);
                break;

            case ($request->input('start_break') == Attendance::STATUS_BREAK):
                Attendance::startBreak($user->id);
                break;

            case ($request->input('end_break') == Attendance::STATUS_WORKING):
                Attendance::endBreak($user->id);
                break;
        }

        return redirect()->route('attendance.show');
    }
}
