<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'break_times';

    protected $fillable = [
        'attendance_id',
        'start_time',
        'end_time',
        'break_time',
    ];

    public static function calculateTodayLatestBreakTime($userId)
    {
        // 今日の一番新しいレコードをEloquentで取得
        $latestBreakTime = self::whereHas('attendance', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->whereDate('date', now()->toDateString()); // 本日の日付
        })
            ->latest('start_time') // start_time が一番新しいレコードを取得
            ->first();

        // start_time と end_time の差分を計算
        $start = \Carbon\Carbon::parse($latestBreakTime->start_time);
        $end = \Carbon\Carbon::parse($latestBreakTime->end_time);

        return $end->diffInMinutes($start); // 差分を分単位で返す
    }

    public static function getMonthBreak($userId, $currentMonth)
    {
        return self::whereHas('attendance', function ($query) use ($userId, $currentMonth) {
            $query->where('user_id', $userId)
                ->whereMonth('date', $currentMonth->month)
                ->whereYear('date', $currentMonth->year);
        })->groupBy('attendance_id')
            ->selectRaw('attendance_id, SUM(break_time) as total_break_time')
            ->get()
            ->map(function ($breakTime) {
                if (is_null($breakTime->total_break_time)) {
                    $breakTime->formatted_break_time = '-';
                } else {
                    $hours = floor($breakTime->total_break_time / 60);
                    $minutes = $breakTime->total_break_time % 60;

                    $breakTime->formatted_break_time = sprintf('%02d:%02d', $hours, $minutes);
                }

                return $breakTime;
            });
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
