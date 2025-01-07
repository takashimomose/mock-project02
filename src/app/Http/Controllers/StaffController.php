<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class StaffController extends Controller
{
    public function index()
    {
        $staffMembers = User::where('role_id', 2)->get(['id', 'name', 'email']);

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

        return view('admin.staff-detail', compact('currentMonth', 'previousMonth', 'nextMonth', 'user', 'attendances', 'breakTimes'));
    }

    public function exportCsv(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $attendances = Attendance::getMonthAttendance($user->id, $currentMonth);
        $breakTimes = BreakTime::getMonthBreak($user->id, $currentMonth)
            ->pluck('formatted_break_time', 'attendance_id'); // 配列に変換 (キー: attendance_id)

        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];

        $csvData = [];
        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->date,
                $attendance->start_time,
                $attendance->end_time,
                $breakTimes[$attendance->id] ?? '-', // 該当する休憩時間がない場合は '-'
                $attendance->working_hours,
            ];
        }

        // CSVを生成するコールバック関数
        $callback = function () use ($csvHeader, $csvData) {
            $file = fopen('php://output', 'w');
            // BOMを追加
            fwrite($file, "\xEF\xBB\xBF");
            // ヘッダーを書き込む
            fputcsv($file, $csvHeader);

            // データを書き込む
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        // ファイル名を設定
        $fileName = sprintf('%s_%s_勤怠情報.csv', $user->name, $currentMonth->format('Y-m'));

        return Response::stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
        ]);
    }
}
