<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'date',
        'start_time',
        'end_time',
        'request_date',
        'correction_status_id',
        'reason',
    ];

    public function breakTimeCorrections()
    {
        return $this->hasMany(BreakTimeCorrection::class, 'attendance_correction_id', 'id');
    }

    const PENDING = 1;   // 承認待ち
    const APPROVED = 2;    // 承認済み

    public static function createCorrectionRequest(array $validatedData)
    {
        // 日付を生成
        $dateString = $validatedData['date_year'] . $validatedData['date_day'];
        $date = Carbon::createFromFormat('Y年n月j日', $dateString)->format('Y-m-d');

        // 開始時間の処理
        $start_time = Carbon::createFromFormat('H:i', $validatedData['start_time'])->format('Y-m-d H:i:s');

        // 終了時間の処理（空の場合はNULLを設定）
        $end_time = !empty($validatedData['end_time']) ? Carbon::createFromFormat('H:i', $validatedData['end_time'])->format('Y-m-d H:i:s') : null;

        // 勤怠修正データを作成
        $attendanceCorrection = self::create([
            'date' => $date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'attendance_id' => $validatedData['attendance_id'],
            'request_date' => Carbon::now(),
            'correction_status_id' => self::PENDING,
            'reason' => $validatedData['reason'],
        ]);

        // 休憩時間の処理（空の場合はNULLを設定）
        if (!empty($validatedData['break_start_time']) && is_array($validatedData['break_start_time'])) {
            foreach ($validatedData['break_start_time'] as $index => $breakStartTime) {
                $formattedBreakStartTime = !empty($breakStartTime) ? Carbon::createFromFormat('H:i', $breakStartTime)->format('Y-m-d H:i:s') : null;
                $formattedBreakEndTime = !empty($validatedData['break_end_time'][$index]) ? Carbon::createFromFormat('H:i', $validatedData['break_end_time'][$index])->format('Y-m-d H:i:s') : null;

                BreakTimeCorrection::create([
                    'start_time' => $formattedBreakStartTime,
                    'end_time' => $formattedBreakEndTime,
                    'attendance_correction_id' => $attendanceCorrection->id,
                ]);
            }
        }

        return $attendanceCorrection;
    }
}
