<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class AttendanceController extends Controller
{

    public function show()
    {
        return view('attendance');
    }
}
