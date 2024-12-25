<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $date = $request->query('date', Carbon::now()->format('Y-m-d'));

        $currentDate = Carbon::createFromFormat('Y-m-d', $date);

        $previousDate = $currentDate->copy()->subDay()->format('Y-m-d');

        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');

        $attendances = Attendance::getAttendancesByDate($date);

        $breakTimes = BreakTime::getDayBreak($date);

        return view('admin.attendance-list', compact('currentDate', 'previousDate', 'nextDate', 'attendances', 'breakTimes'));
    }
}
