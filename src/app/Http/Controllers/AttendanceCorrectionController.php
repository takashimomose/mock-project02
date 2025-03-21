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
    public function correctGeneral(AttendanceCorrectionRequest $request)
    {
        $validatedData = $request->validated();

        AttendanceCorrection::createCorrectionRequest($validatedData);

        return redirect()->route('attendance.index');
    }

    public function correctAdmin(AttendanceCorrectionRequest $request)
    {
        $validatedData = $request->validated();

        $attendance = Attendance::findOrFail($validatedData['attendance_id']);
        $attendance->updateAttendance($validatedData);

        return redirect()->route('admin.attendance.index');
    }


    public static function correct_index()
    {
        $currentUser = auth()->user();

        $pendingCorrections = AttendanceCorrection::getCorrectionsByStatus($currentUser, AttendanceCorrection::PENDING);
        $approvedCorrections = AttendanceCorrection::getCorrectionsByStatus($currentUser, AttendanceCorrection::APPROVED);

        return view('request-list', compact('pendingCorrections', 'approvedCorrections'));
    }

    public function show($correctionId)
    {
        // attendance_correctionsのIDからAttendanceを取得
        $latestCorrection = AttendanceCorrection::findOrFail($correctionId);

        $attendanceDetails = Attendance::getAttendanceDetailsWithCorrection($latestCorrection->attendance_id);

        return view('admin.approve', compact('attendanceDetails'));
    }

    public function approve(Request $request)
    {
        $attendance = Attendance::findOrFail($request->attendance_id);

        $attendanceData = $request->only(['attendance_id', 'correction_id', 'date_year', 'date_day', 'start_time', 'end_time', 'reason', 'break_start_time', 'break_end_time']);

        $attendance->updateAttendance($attendanceData);

        AttendanceCorrection::where('attendance_id', $attendanceData['attendance_id'])
            ->update(['correction_status_id' => AttendanceCorrection::APPROVED]);

        return redirect()->route('correction.show', ['id' => $request->correction_id]);
    }
}
