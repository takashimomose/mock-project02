<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staffMembers = User::where('role_id', User::ROLE_GENERAL)->get(['id', 'name', 'email']);

        return view('admin.staff-list', compact('staffMembers'));
    }

    public function detail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', Carbon::now()->format('Y-m'));

        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');

        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::getMonthAttendance($user->id, $currentMonth);

        $breakTimes = BreakTime::getMonthBreak($user->id, $currentMonth);

        return view('admin.staff-detail', compact('currentMonth', 'previousMonth', 'nextMonth', 'user', 'attendances', 'breakTimes' ));
    }
}
