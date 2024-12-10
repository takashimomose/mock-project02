<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // リクエストされた月を取得。デフォルトは現在の月。
        $month = $request->query('month', Carbon::now()->format('Y-m'));

        // Carbonインスタンスを作成
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');

        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::getMonthAttendance($user->id, $currentMonth);

        $breakTimes = BreakTime::getMonthBreak($user->id, $currentMonth);

        return view('attendance-list', compact('currentMonth', 'previousMonth', 'nextMonth', 'attendances', 'breakTimes'));
    }
}
