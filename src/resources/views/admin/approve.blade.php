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
                        <td colspan="3">{{ $attendanceDetail['name'] }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <span class="year">
                                {{ $attendanceDetail['date_year'] }}</span>
                            <input class="form-input" type="hidden" name="date_year"
                                value="{{ $attendanceDetail['date_year'] }}">
                        </td>
                        <td></td>
                        <td>
                            {{ $attendanceDetail['date_day'] }}
                            <input class="form-input" type="hidden" name="date_day"
                                value="{{ $attendanceDetail['date_day'] }}">
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            {{ $attendanceCorrection['start_time'] }}
                            <input class="form-input" type="hidden" name="start_time"
                                value="{{ $attendanceCorrection['start_time'] }}">
                        </td>
                        <td>～</td>
                        <td>
                            {{ $attendanceCorrection['end_time'] }}
                            <input class="form-input" type="hidden" name="end_time"
                                value="{{ $attendanceCorrection['end_time'] }}">
                        </td>
                    </tr>
                    @foreach ($attendanceCorrection['break_times'] as $index => $break)
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
                            <span class="reason">{{ $attendanceCorrection['reason'] }}</span>
                            <input class="form-input-reason" type="hidden" name="reason"
                                value="{{ $attendanceCorrection['reason'] }}">
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="attendance_id" value="{{ $attendanceDetail['attendance_id'] }}">
                @if ($attendanceCorrection->isApprovedOrEmpty())
                    <p class="approved">承認済み</p>
                @elseif ($attendanceCorrection->isPending())
                    <button class="edit-btn" type="submit">承認</button>
                @endif
            </form>
        </section>
    </main>
@endsection
