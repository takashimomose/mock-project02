<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function detail()
    {
        $user = Auth::user();
        
        return view('attendance-detail', compact('user'));
    }
}
