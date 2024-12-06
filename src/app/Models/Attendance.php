<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'attendance_status_id',
    ];

    /* 今日の勤怠レコードを取得 */
    public function scopeTodayRecord($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->where('date', now()->toDateString());
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    /* 出勤記録を作成 */
    public static function startWork($userId)
    {
        return self::create([
            'user_id' => $userId,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'attendance_status_id' => 2,
        ]);
    }

    /* 退勤記録を更新 */
    public static function endWork($userId)
    {
        return self::todayRecord($userId)
            ->update([
                'end_time' => now(),
                'attendance_status_id' => 4,
            ]);
    }

    /* 休憩開始の処理 */
    public static function startBreak($userId)
    {
        $attendance = self::todayRecord($userId)->first();
        if ($attendance) {
            $attendance->update(['attendance_status_id' => 3]);

            BreakTime::create([
                'user_id' => $userId,
                'attendance_id' => $attendance->id,
                'start_time' => now(),
            ]);
        }
    }

    /* 休憩終了の処理 */
    public static function endBreak($userId)
    {
        $attendance = self::todayRecord($userId)->first();
        if ($attendance) {
            $attendance->update(['attendance_status_id' => 2]);

            $attendance->breakTimes()->whereNull('end_time')->update([
                'end_time' => now(),
            ]);
        }
    }
}
