@extends('layouts.app')

@section('title', '修正申請承認画面')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endpush

@section('content')

    <main class="wrapper">
        <section class="attendance-detail-section">
            <h1>勤怠詳細</h1>
            <form action="{{ route('correction.approve') }}" method="POST">
                @csrf
                <table class="attendance-detail-table">
                    <tr>
                        <th>名前</th>
                        <td colspan="3">{{ $attendanceDetails['name'] }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <span class="year">
                                {{ $attendanceDetails['date_year'] }}</span>
                            <input class="form-input" type="hidden" name="date_year"
                                value="{{ $attendanceDetails['date_year'] }}">
                        </td>
                        <td></td>
                        <td>
                            {{ $attendanceDetails['date_day'] }}
                            <input class="form-input" type="hidden" name="date_day"
                                value="{{ $attendanceDetails['date_day'] }}">
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            {{ $attendanceDetails['start_time'] }}
                            <input class="form-input" type="hidden" name="start_time"
                                value="{{ $attendanceDetails['start_time'] }}">
                        </td>
                        <td>～</td>
                        <td>
                            {{ $attendanceDetails['end_time'] }}
                            <input class="form-input" type="hidden" name="end_time"
                                value="{{ $attendanceDetails['end_time'] }}">
                        </td>
                    </tr>
                    @foreach ($attendanceDetails['break_times'] as $break)
                        <tr>
                            @if ($loop->first)
                                <th>休憩</th>
                            @else
                                <th></th>
                            @endif
                            <td>
                                {{ $break['start_time'] }}
                                <input class="form-input" type="hidden" name="break_start_time[]"
                                    value="{{ $break['start_time'] }}">
                            </td>
                            <td>～</td>
                            <td>
                                {{ $break['end_time'] }}
                                <input class="form-input" type="hidden" name="break_end_time[]"
                                    value="{{ $break['end_time'] }}">
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <span class="reason">{{ $attendanceDetails['reason'] }}</span>
                            <input class="form-input-reason" type="hidden" name="reason"
                                value="{{ $attendanceDetails['reason'] }}">
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="attendance_id" value="{{ $attendanceDetails['attendance_id'] }}">
                <input type="hidden" name="correction_id" value="{{ $attendanceDetails['correction_id'] }}">
                @if ($attendanceDetails['correction_status_id'] == \App\Models\AttendanceCorrection::APPROVED)
                    <!-- 1が「承認済み」を示す場合 -->
                    <p class="approved">承認済み</p>
                @elseif ($attendanceDetails['correction_status_id'] == \App\Models\AttendanceCorrection::PENDING)
                    <!-- 2が「保留中」を示す場合 -->
                    <button class="edit-btn" type="submit">承認</button>
                @endif
            </form>
        </section>
    </main>
@endsection
