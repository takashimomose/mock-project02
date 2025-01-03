<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCorrectionController extends Controller
{
    public function correct(AttendanceCorrectionRequest $request)
    {
        $validatedData = $request->validated();
        $user = Auth::user();

        if ($user->role_id == User::ROLE_GENERAL) {

            AttendanceCorrection::createCorrectionRequest($validatedData);

            return redirect()->route('attendance.index');
        }

        if ($user->role_id == User::ROLE_ADMIN) {

            $attendance = Attendance::findOrFail($validatedData['attendance_id']);
            $attendance->updateAttendance($validatedData);

            return redirect()->route('admin.attendance.index');
        }
    }


    public static function correct_index()
    {
        $currentUser = auth()->user();

        $pendingCorrections = AttendanceCorrection::getCorrectionsByStatus($currentUser, AttendanceCorrection::PENDING);
        $approvedCorrections = AttendanceCorrection::getCorrectionsByStatus($currentUser, AttendanceCorrection::APPROVED);

        return view('request-list', compact('pendingCorrections', 'approvedCorrections'));
    }

    public function show($attendanceId)
    {
        $attendanceDetail = Attendance::getAttendanceDetail($attendanceId);

        $attendanceCorrection = AttendanceCorrection::getCorrectionRequest($attendanceId);

        return view('admin.approve', compact('attendanceDetail', 'attendanceCorrection'));
    }

    public function approve(Request $request)
    {
        // $attendance_idを使用してAttendanceレコードを取得
        $attendance = Attendance::findOrFail($request->attendance_id);
        // dd($attendance);  
        // $requestから必要なデータを配列として抽出して渡す
        $attendanceData = $request->only(['attendance_id', 'date_year', 'date_day', 'start_time', 'end_time', 'reason', 'break_start_time', 'break_end_time']);

        // AttendanceのupdateAttendanceメソッドに配列を渡す
        $attendance->updateAttendance($attendanceData);
   
        return redirect()->route('correction.show', ['attendance_id' => $attendance->attendance_id]);
    }
}
