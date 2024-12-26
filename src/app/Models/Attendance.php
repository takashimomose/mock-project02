<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'working_hours',
        'attendance_status_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    public function attendanceCorrection()
    {
        return $this->hasMany(AttendanceCorrection::class, 'attendance_id');
    }

    const STATUS_BEFORE = 1;   // 勤務外
    const STATUS_WORKING = 2;    // 勤務中
    const STATUS_BREAK = 3;      // 休憩中
    const STATUS_FINISHED = 4; // 退勤済

    /* 今日の勤怠レコードを取得 */
    public static function getTodayRecord($userId)
    {
        return self::where('user_id', $userId)
            ->where('date', now()->toDateString())
            ->first();
    }

    /* 出勤記録を作成 */
    public static function startWork($userId)
    {
        return self::create([
            'user_id' => $userId,
            'date' => now()->toDateString(),
            'start_time' => now(),
            'attendance_status_id' => self::STATUS_WORKING,
        ]);
    }

    /* 退勤記録を更新（退勤時間と勤務時間を挿入） */
    public static function endWork($userId)
    {
        $record = self::getTodayRecord($userId);

        $startTime = $record->start_time;
        $endTime = now();

        $workingMinutes = Carbon::parse($startTime)->diffInMinutes($endTime) % 60;

        return $record->update([
            'end_time' => $endTime,
            'attendance_status_id' => self::STATUS_FINISHED,
            'working_hours' => $workingMinutes,
        ]);
    }

    /* 休憩開始の処理 */
    public static function startBreak($userId)
    {
        $attendance = self::getTodayRecord($userId);
        if ($attendance) {
            $attendance->update(['attendance_status_id' => self::STATUS_BREAK]);

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
        $attendance = self::getTodayRecord($userId);
        if ($attendance) {
            $attendance->update(['attendance_status_id' => self::STATUS_WORKING]);

            $attendance->breakTimes()->whereNull('end_time')->update([
                'end_time' => now(),
            ]);
        }
    }

    /* 月の勤怠データ取得 */
    public static function getMonthAttendance($userId, $currentMonth)
    {
        return self::where('user_id', $userId)
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->get()
            ->map(function ($attendance) {
                if (is_null($attendance->start_time)) {
                    $attendance->start_time = '-';
                } else {
                    $attendance->start_time = Carbon::parse($attendance->start_time)->format('H:i');
                }

                if (is_null($attendance->end_time)) {
                    $attendance->end_time = '-';
                } else {
                    $attendance->end_time = Carbon::parse($attendance->end_time)->format('H:i');
                }

                if (is_null($attendance->working_hours)) {
                    $attendance->working_hours = '-';
                } else {
                    $hours = floor($attendance->working_hours / 60);
                    $minutes = $attendance->working_hours % 60;

                    $attendance->working_hours = sprintf('%02d:%02d', $hours, $minutes);
                }

                return $attendance;
            });
    }

    /* attendance_id別の勤怠データ取得 */
    public static function getAttendanceDetail($attendanceId)
    {
        $attendance = self::with(['user:id,name', 'breakTimes:id,attendance_id,start_time,end_time'])
            ->findOrFail($attendanceId);

        $latestCorrection = AttendanceCorrection::where('attendance_id', $attendanceId)
            ->latest('request_date')
            ->first();

        $data = [
            'name' => $attendance->user->name,
            'attendance_id' => $attendance->id,
            'date_year' => null,
            'date_day' => null,
            'start_time' => null,
            'end_time' => null,
            'correction_status_id' => $latestCorrection->correction_status_id ?? null,
            'reason' => $latestCorrection->reason ?? null,
            'break_times' => [],
        ];

        if ($attendance->date) {
            $data['date_year'] = Carbon::parse($attendance->date)->format('Y年');
            $data['date_day'] = Carbon::parse($attendance->date)->format('n月j日');
        }

        if ($attendance->start_time) {
            $data['start_time'] = Carbon::parse($attendance->start_time)->format('H:i');
        }

        if ($attendance->end_time) {
            $data['end_time'] = Carbon::parse($attendance->end_time)->format('H:i');
        }

        $data['break_times'] = $attendance->breakTimes->map(function ($break) {
            $breakData = [
                'start_time' => null,
                'end_time' => null,
            ];

            if ($break->start_time) {
                $breakData['start_time'] = Carbon::parse($break->start_time)->format('H:i');
            }

            if ($break->end_time) {
                $breakData['end_time'] = Carbon::parse($break->end_time)->format('H:i');
            }

            return $breakData;
        });

        return $data;
    }

    // 指定された日付でデータを取得
    public static function getAttendancesByDate($date)
    {
        return self::where('date', $date)
            ->with(['user:id,name'])
            ->get(['user_id', 'id', 'start_time', 'end_time', 'working_hours'])
            ->map(function ($date) {
                if (is_null($date->start_time)) {
                    $date->start_time = '-';
                } else {
                    $date->start_time = Carbon::parse($date->start_time)->format('H:i');
                }

                if (is_null($date->end_time)) {
                    $date->end_time = '-';
                } else {
                    $date->end_time = Carbon::parse($date->end_time)->format('H:i');
                }
                if (is_null($date->working_hours)) {
                    $date->working_hours = '-';
                } else {
                    $hours = floor($date->working_hours / 60);
                    $minutes = $date->working_hours % 60;

                    $date->working_hours = sprintf('%02d:%02d', $hours, $minutes);
                }

                return $date;
            });
    }

    public static function correctAttendanceAndBreakTimes(array $validatedData)
    {
        // 条件判定用データ
        $startTime = $validatedData['start_time'] ?? null;
        $endTime = $validatedData['end_time'] ?? null;
        $breakStartTimes = $validatedData['break_start_time'] ?? [];
        $breakEndTimes = $validatedData['break_end_time'] ?? [];

        // 条件1: end_time が null かつ break_start_time に1つだけ値が存在し、break_end_time がすべて null
        $condition1 = is_null($endTime)
            && count(array_filter($breakStartTimes, fn($time) => !is_null($time))) === 1
            && count(array_filter($breakEndTimes, fn($time) => !is_null($time))) === 0;

        // 条件2: end_time が null かつ break_end_time の最後が null
        $condition2 = is_null($endTime) && end($breakEndTimes) === null;

        // 条件3: 休憩の配列の最後の break_start_time と break_end_time だけ値がある場合
        $condition3 = is_null($endTime)
            && count(array_filter($breakStartTimes, fn($time) => is_null($time))) === 0
            && count(array_filter($breakEndTimes, fn($time) => is_null($time))) === 0;

        // 条件4: 休憩の配列が空の場合
        $condition4 = empty(array_filter($breakStartTimes, fn($time) => !is_null($time)))
            && empty(array_filter($breakEndTimes, fn($time) => !is_null($time)));

        // 条件5: 休憩の配列が空でない場合
        $condition5 = !empty($breakStartTimes) && !in_array(null, $breakStartTimes, true)
            && !empty($breakEndTimes) && !in_array(null, $breakEndTimes, true);

        // 条件を満たさない場合は処理を中断
        if (!$condition1 && !$condition2 && !$condition3 && !$condition4 && !$condition5) {
            return;
        }

        // 勤怠データの処理
        $attendanceStatusId = ($condition3) ? 2 : (($condition4 || $condition5) ? 4 : 3);

        $workingMinutes = !is_null($endTime)
            ? Carbon::parse($startTime)->diffInMinutes($endTime)
            : null; // $endTimeがnullの場合は0を設定

        $attendance = Attendance::updateOrCreate(
            ['id' => $validatedData['attendance_id']],
            [
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $endTime,
                'working_hours' =>  $workingMinutes,
                'attendance_status_id' => $attendanceStatusId,
            ]
        );

        // dd($attendance );

        // 既存の休憩データを削除
        $attendance->breakTimes()->delete();

        // 条件ごとの処理
        if ($condition1 || $condition2 || $condition3 || $condition5) {
            foreach ($breakStartTimes as $index => $startTime) {
                $breakEndTime = $breakEndTimes[$index] ?? null;

                if (!is_null($startTime) && !is_null($breakEndTime)) {
                    $startTimeInSeconds = strtotime($startTime);
                    $endTimeInSeconds = strtotime($breakEndTime);

                    if ($endTimeInSeconds > $startTimeInSeconds) {
                        $breakTimeInMinutes = ($endTimeInSeconds - $startTimeInSeconds) / 60;

                        BreakTime::updateOrCreate(
                            ['attendance_id' => $attendance->id, 'start_time' => $startTime],
                            [
                                'start_time' => $startTime,
                                'end_time' => $breakEndTime,
                                'break_time' => $breakTimeInMinutes,
                            ]
                        );
                    }
                } elseif (!is_null($startTime) && is_null($breakEndTime)) {
                    BreakTime::updateOrCreate(
                        ['attendance_id' => $attendance->id, 'start_time' => $startTime],
                        [
                            'start_time' => $startTime,
                            'end_time' => null,
                            'break_time' => null,
                        ]
                    );
                }
            }
        }

        // 条件4: 処理を特にしないが attendance_status_id=4 として設定済み
    }
}
