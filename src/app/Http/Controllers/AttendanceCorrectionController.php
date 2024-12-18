<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionController extends Controller
{
    public function correct(AttendanceCorrectionRequest $request)
    {
        $validatedData = $request->validated();

        AttendanceCorrection::createCorrectionRequest($validatedData);

        return redirect()->route('attendance.index');
    }
}
