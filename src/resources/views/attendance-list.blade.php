@extends('layouts.app')

@section('title', '勤怠一覧')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endpush

@section('content')

    <main class="wrapper">
        <section class="attendance-list-section">
            <h1>勤怠一覧</h1>
            <div class="attendance-header">
                <a href="{{ url('/attendance/list?month=' . $previousMonth) }}" class="prev-btn">&larr; 前月</a>
                <span class="current-month">
                    <img src="{{ asset('images/calendar.svg') }}" class="calendar-icon" alt="calendar-icon">
                    {{ $currentMonth->format('Y/n') }}
                </span>
                <a href="{{ url('/attendance/list?month=' . $nextMonth) }}" class="next-btn">翌月 &rarr;</a>
            </div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
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
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('M/D') .
                                '(' .
                                ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($attendance->date)->dayOfWeek] .
                                ')' }}
                            </td>
                            <td>{{ $attendance->start_time }}
                            </td>
                            <td>{{ $attendance->end_time }}
                            </td>
                            <td>{{ optional($breakTimes->firstWhere('attendance_id', $attendance->id))->formatted_break_time ?? '-' }}
                            </td>
                            <td>{{ $attendance->working_hours }}
                            </td>
                            <td><a href="#" class="details-link">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection