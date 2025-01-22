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
        'old_date',
        'date',
        'start_time',
        'end_time',
        'request_date',
        'correction_status_id',
        'reason',
    ];

    const PENDING = 1;   // 承認待ち
    const APPROVED = 2;    // 承認済み

    public function breakTimeCorrections()
    {
        return $this->hasMany(BreakTimeCorrection::class, 'attendance_correction_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public static function createCorrectionRequest(array $validatedData)
    {
        // 修正後の日付を生成
        $dateString = $validatedData['date_year'] . $validatedData['date_day'];
        $date = Carbon::createFromFormat('Y年n月j日', $dateString)->format('Y-m-d');

        // 修正前の日付を作成
        $oldDateString = $validatedData['old_date_year'] . $validatedData['old_date_day'];
        $oldDate = Carbon::createFromFormat('Y年n月j日', $oldDateString)->format('Y-m-d');

        // 開始時間の処理
        $start_time = Carbon::createFromFormat('H:i', $validatedData['start_time'])->format('Y-m-d H:i:s');

        // 終了時間の処理（空の場合はNULLを設定）
        $end_time = !empty($validatedData['end_time']) ? Carbon::createFromFormat('H:i', $validatedData['end_time'])->format('Y-m-d H:i:s') : null;

        // 勤怠修正データを作成
        $attendanceCorrection = self::create([
            'old_date' => $oldDate,
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

    public function isPending()
    {
        return $this->correction_status_id === self::PENDING;
    }

    public function isApprovedOrEmpty()
    {
        return !$this->correction_status_id || $this->correction_status_id === self::APPROVED;
    }

    public static function getCorrectionsByStatus($currentUser, $status)
    {
        $query = self::where('correction_status_id', $status)
            ->with(['attendance', 'attendance.user']);

        // ユーザーのロールに応じて条件を追加
        if ($currentUser->role === User::ROLE_GENERAL) {
            // ROLE_GENERALの場合、自分のattendanceのみを対象とする
            $query->whereHas('attendance', function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->id);
            });
        }

        $statusLabel = ($status === self::PENDING) ? '承認待ち' : '承認済み';

        return $query->get()->map(function ($correction) use ($statusLabel) {
            return [
                'attendance_id' => $correction->attendance->id,
                'correction_id' => $correction->id,
                'correction_status_id' => $statusLabel,
                'name' => $correction->attendance->user->name,
                'old_date' => Carbon::parse($correction->old_date)->format('n月j日'),
                'reason' => $correction->reason,
                'request_date' => Carbon::parse($correction->request_date)->format('n月j日'),
            ];
        });
    }

    public static function getCorrectionRequest($attendanceId)
    {
        // 勤怠修正レコードを取得
        $query = self::where('attendance_id', $attendanceId)
            ->latest('request_date')
            ->with('breakTimeCorrections') // 関連するBreakTimeCorrectionを一緒に取得
            ->first();

        // $queryがnullでないことを確認
        if (!$query) {
            return null; // nullを返す
        }

        // 勤怠修正レコードのstart_timeとend_timeを取得
        $query->start_time = Carbon::parse($query->start_time)->format('H:i') ?? null;
        $query->end_time = Carbon::parse($query->end_time)->format('H:i') ?? null;
        $query->reason = $query->reason ?? null;
        $query->correction_status_id = $query->correction_status_id ?? null;

        // 休憩修正レコードのstart_timeとend_timeを取得
        $query->break_times = $query->breakTimeCorrections->map(function ($breakTime) {
            return [
                'start_time' => Carbon::parse($breakTime->start_time)->format('H:i'),
                'end_time' => Carbon::parse($breakTime->end_time)->format('H:i'),
            ];
        })->toArray();

        return $query; // オブジェクトとして返す
    }

    // 勤怠修正依頼データ（休憩時間分）取得
    public static function getLatestCorrection($attendanceId)
    {
        return self::with('breakTimeCorrections')
            ->where('attendance_id', $attendanceId)
            ->latest('request_date')
            ->first();
    }
}
