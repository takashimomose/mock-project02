@extends('layouts.app')

@section('title', '勤怠詳細')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')

    @php
        use App\Models\AttendanceCorrection;
    @endphp

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
                            @if ($attendanceCorrection->isPending())
                                <span class="year">
                                    {{ $attendanceDetail['date_year'] }}</span>
                            @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                <input class="form-input" type="text" name="date_year"
                                    value="{{ old('date_year', $attendanceDetail['date_year']) }}">
                            @endif
                            @error('date_year')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td></td>
                        <td>
                            @if ($attendanceCorrection->isPending())
                                {{ $attendanceDetail['date_day'] }}
                            @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                <input class="form-input" type="text" name="date_day"
                                    value="{{ old('date_day', $attendanceDetail['date_day']) }}">
                            @endif
                            @error('date_day')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            @if ($attendanceCorrection->isPending())
                                {{ $attendanceDetail['start_time'] }}
                            @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                <input class="form-input" type="text" name="start_time"
                                    value="{{ old('start_time', $attendanceDetail['start_time']) }}">
                            @endif
                            @error('start_time')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>～</td>
                        <td>
                            @if ($attendanceCorrection->isPending())
                                {{ $attendanceDetail['end_time'] }}
                            @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                <input class="form-input" type="text" name="end_time"
                                    value="{{ old('end_time', $attendanceDetail['end_time']) }}">
                            @endif
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
                                @unless ($attendanceCorrection->isPending())
                                    <input class="form-input" type="text" name="break_start_time[0]"
                                        value="{{ old('break_start_time.0', '') }}">
                                    @error('break_start_time.0')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                @endunless
                            </td>
                            <td>～</td>
                            <td>
                                @unless ($attendanceCorrection->isPending())
                                    <input class="form-input" type="text" name="break_end_time[0]"
                                        value="{{ old('break_end_time.0', '') }}">
                                    @foreach (['break_end_time.0', 'break_time_before_end_time', 'break_within_working_hours'] as $errorKey)
                                        @error($errorKey)
                                            <div class="error-message">{{ $message }}</div>
                                        @enderror
                                    @endforeach
                                @endunless
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
                                    @if ($attendanceCorrection->isPending())
                                        {{ $break['start_time'] }}
                                    @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                        <input class="form-input" type="text" name="break_start_time[]"
                                            value="{{ old('break_start_time.' . $index, $break['start_time']) }}">
                                    @endif
                                    @error('break_start_time.' . $index)
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td>～</td>
                                <td>
                                    @if ($attendanceCorrection->isPending())
                                        {{ $break['end_time'] }}
                                    @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                        <input class="form-input" type="text" name="break_end_time[]"
                                            value="{{ old('break_end_time.' . $index, $break['end_time']) }}">
                                    @endif
                                    @foreach (['break_end_time.' . $index, 'break_time_before_end_time', 'break_within_working_hours'] as $errorKey)
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
                            @if ($attendanceCorrection->isPending())
                                <span class="reason">{{ old('reason', $attendanceDetail['reason']) }}</span>
                            @elseif ($attendanceCorrection->isApprovedOrEmpty())
                                <textarea class="form-input-reason" name="reason">{{ old('reason', '') }}</textarea>
                            @endif
                            @error('reason')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="attendance_id"
                    value="{{ old('attendance_id', $attendanceDetail['attendance_id']) }}">
                @if ($attendanceCorrection->isApprovedOrEmpty())
                    <button class="edit-btn" type="submit">修正</button>
                @elseif ($attendanceCorrection->isPending())
                    <p class="message">*承認待ちのため修正はできません。</p>
                @endif
            </form>
        </section>
    </main>
@endsection
