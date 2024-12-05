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

        $attendance = Attendance::todayRecord($user->id)->first();

        if (!$attendance) {
            $workingStatus = 1;
        } elseif ($attendance->start_time !== null && $attendance->break_start_time === null) {
            $workingStatus = 2;
        } elseif ($attendance->break_start_time !== null && $attendance->end_time === null) {
            $workingStatus = 3;
        } elseif ($attendance->end_time !== null) {
            $workingStatus = 4;
        }

        return view('attendance', compact('user', 'workingStatus'));
    }

    public function store(Request $request)
    {
        $workingStatus = 1だったらbuttonのvalueのstart_workを使ってattendancesテーブルのdate, 

        return redirect()->route('attendance.show');
    }
}
