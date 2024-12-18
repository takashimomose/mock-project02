<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
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

    public function store(Request $request)
    {
        $user = Auth::user();

        switch (true) {
            case ($request->input('start_work') == Attendance::STATUS_WORKING):
                Attendance::startWork($user->id);
                break;

            case ($request->input('end_work') == Attendance::STATUS_FINISHED):
                Attendance::endWork($user->id);
                break;

            case ($request->input('start_break') == Attendance::STATUS_BREAK):
                Attendance::startBreak($user->id);
                break;

            case ($request->input('end_break') == Attendance::STATUS_WORKING):
                Attendance::endBreak($user->id);

                // 今日の最新の休憩時間を計算
                $breakTimeDifference = BreakTime::calculateTodayLatestBreakTime($user->id);

                // 今日の最新のレコードをEloquentで取得
                $latestBreakRecord = BreakTime::whereHas('attendance', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->whereDate('date', now()->toDateString());
                })
                    ->latest('start_time') // 最新のレコードを取得
                    ->first(); // 最新の1件を取得

                // break_timeを更新
                $latestBreakRecord->update(['break_time' => $breakTimeDifference]);
                break;
        }

        return redirect()->route('attendance.show');
    }

    public function show()
    {
        $user = Auth::user();
        $workingStatus = null;

        $attendance = Attendance::getTodayRecord($user->id);

        if (!$attendance) {
            $workingStatus = Attendance::STATUS_BEFORE;
        } else {
            switch ($attendance->attendance_status_id) {
                case Attendance::STATUS_WORKING:
                    $workingStatus = Attendance::STATUS_WORKING;
                    break;

                case Attendance::STATUS_BREAK:
                    $workingStatus = Attendance::STATUS_BREAK;
                    break;

                case Attendance::STATUS_FINISHED:
                    $workingStatus = Attendance::STATUS_FINISHED;
                    break;
            }
        }

        return view('attendance', compact('user', 'workingStatus'));
    }

    public function detail($attendanceId)
    {
        $attendanceDetail = Attendance::getAttendanceDetail($attendanceId);

        $attendanceCorrection = new AttendanceCorrection();

        return view('attendance-detail', compact('attendanceDetail', 'attendanceCorrection'));
    }
}
