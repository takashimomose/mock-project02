<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date_year' => ['required', 'date_format:Y年',],
            'date_day' => ['required', 'date_format:m月d日',],
            'start_time' => ['required', 'date_format:H:i',],
            'end_time' => ['nullable', 'date_format:H:i',],
            'break_start_time.*' => ['nullable', 'date_format:H:i',],
            'break_end_time.*' => ['nullable', 'date_format:H:i',],
            'reason' => ['required'],
            'attendance_id' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'date_year.required' => '年を入力してください',
            'date_year.date_format' => '入力例：2024年',
            'date_day.required' => '月日を入力してください',
            'date_day.date_format' => '入力例：01月01日',
            'start_time.required' => '出勤時間を入力してください',
            'start_time.date_format' => '入力例：09:00',
            'end_time.date_format' => '入力例：18:00',
            'break_start_time.*.date_format' => '入力例：12:00',
            'break_end_time.*.date_format' => '入力例：13:00',
            'reason.required' => '備考を入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->input('start_time');
            $endTime = $this->input('end_time');
            $breakStartTime = $this->input('break_start_time');
            $breakEndTime = $this->input('break_end_time');

            // 出勤時間が退勤時間より遅くないかチェック
            if ($startTime && $endTime && strtotime($startTime) >= strtotime($endTime)) {
                $validator->errors()->add('start_time_before_end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩開始時間が終了時間より遅くないかチェック
            foreach ($breakStartTime as $index => $breakStart) {
                $breakEnd = $breakEndTime[$index] ?? null;
                if ($breakStart && $breakEnd && strtotime($breakStart) >= strtotime($breakEnd)) {
                    $validator->errors()->add('break_time_before_end_time', '休憩開始時間もしくは休憩終了時間が不適切な値です');
                }
            }

            // 休憩時間が勤務時間内かチェック
            foreach ($breakStartTime as $index => $breakStart) {
                $breakEnd = $breakEndTime[$index] ?? null;
                if ($breakStart && $breakEnd) {
                    // 休憩時間が勤務時間外の場合のチェック
                    if (strtotime($breakStart) < strtotime($startTime) || strtotime($breakEnd) > strtotime($endTime)) {
                        $validator->errors()->add('break_within_working_hours', '休憩時間が勤務時間外です');
                    }
                }
            }
        });
    }
}
