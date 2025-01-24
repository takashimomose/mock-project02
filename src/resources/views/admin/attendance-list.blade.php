@extends('layouts.app')

@section('title', '勤怠一覧')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <section class="attendance-list-section">
            <h1>{{ $currentDate->format('Y年m月d日') }}</h1>
            <div class="attendance-header">
                <a href="{{ url('admin/attendance/list?date=' . $previousDate) }}" class="prev-btn">&larr; 前日</a>
                <span class="current-month">
                    <img src="{{ asset('images/calendar.svg') }}" class="calendar-icon" alt="calendar-icon">
                    {{ $currentDate->format('Y/n') }}
                </span>
                <a href="{{ url('admin/attendance/list?date=' . $nextDate) }}" class="next-btn">翌日 &rarr;</a>
            </div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ $attendance->start_time }}</td>
                            <td>{{ $attendance->end_time }}</td>
                            <td>
                                @foreach ($breakTimes as $breakTime)
                                    @if ($breakTime->attendance_id == $attendance->id)
                                        {{ $breakTime->formatted_break_time }}
                                    @endif
                                @endforeach
                            </td>
                            <td>{{ $attendance->working_hours }}</td>
                            <td><a href="{{ route('attendance.detail', ['attendance_id' => $attendance->id]) }}" class="details-link">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection
