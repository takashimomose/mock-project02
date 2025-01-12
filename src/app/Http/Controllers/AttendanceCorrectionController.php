<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\User;
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
}
