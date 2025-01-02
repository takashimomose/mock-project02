@extends('layouts.app')

@section('title', '管理者勤怠詳細')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')

    <main class="wrapper">
        <section class="attendance-detail-section">
            <h1>勤怠詳細</h1>
            <form action="{{ route('attendance.correct') }}" method="POST">
                @csrf
                <table class="attendance-detail-table">
                    <tr>
                        <th>名前</th>
                        <td colspan="3">{{ $attendanceDetail['name'] }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <input class="form-input" type="text" name="date_year"
                                value="{{ old('date_year', $attendanceDetail['date_year']) }}">
                            @error('date_year')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td></td>
                        <td>
                            <input class="form-input" type="text" name="date_day"
                                value="{{ old('date_day', $attendanceDetail['date_day']) }}">
                            @error('date_day')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <input class="form-input" type="text" name="start_time"
                                value="{{ old('start_time', $attendanceDetail['start_time']) }}">
                            @error('start_time')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>～</td>
                        <td>
                            <input class="form-input" type="text" name="end_time"
                                value="{{ old('end_time', $attendanceDetail['end_time']) }}">
                            @foreach (['end_time', 'start_time_before_end_time'] as $errorKey)
                                @error($errorKey)
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            @endforeach
                        </td>
                    </tr>
                    @if ($attendanceDetail['break_times']->isEmpty())
                        <tr>
                            <th>休憩</th>
                            <td>
                                <input class="form-input" type="text" name="break_start_time[0]"
                                    value="{{ old('break_start_time.0', '') }}">
                                @error('break_start_time.0')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>～</td>
                            <td>
                                <input class="form-input" type="text" name="break_end_time[0]"
                                    value="{{ old('break_end_time.0', '') }}">
                                @foreach (['break_end_time.0', 'break_time_before_end_time', 'break_within_working_hours'] as $errorKey)
                                    @error($errorKey)
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            </td>
                        </tr>
                    @else
                        @foreach ($attendanceDetail['break_times'] as $index => $break)
                            <tr>
                                @if ($loop->first)
                                    <th>休憩</th>
                                @else
                                    <th></th>
                                @endif
                                <td>
                                    <input class="form-input" type="text" name="break_start_time[]"
                                        value="{{ old('break_start_time.' . $index, $break['start_time']) }}">
                                    @foreach (['break_start_time.' . $index, 'break_start_time_empty.' . $index] as $errorKey)
                                        @error($errorKey)
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                </td>
                                <td>～</td>
                                <td>
                                    <input class="form-input" type="text" name="break_end_time[]"
                                        value="{{ old('break_end_time.' . $index, $break['end_time']) }}">
                                    @foreach (['break_time_before_end_time.' . $index, 'break_within_working_hours.' . $index] as $errorKey)
                                        @error($errorKey)
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    <tr>
                        <th>備考</th>
                        <td colspan="3">
                            <textarea class="form-input-reason" name="reason">{{ old('reason', $attendanceDetail['reason']) }}</textarea>
                            @error('reason')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="attendance_id"
                    value="{{ old('attendance_id', $attendanceDetail['attendance_id']) }}">
                <button class="edit-btn" type="submit">修正</button>
            </form>
        </section>
    </main>
@endsection
