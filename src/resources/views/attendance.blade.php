@extends('layouts.app')

@section('title', '勤怠登録')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <section class="attendance-section">
            <div class="container">
                <div class="status">勤務外</div>
                <div class="date">
                    {{ \Carbon\Carbon::now()->isoFormat('Y年M月D日') .
                        '(' .
                        ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::now()->dayOfWeek] .
                        ')' }}
                </div>
                <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>
                <button type="submit" class="start-btn" value="start_work">出勤</button>
            </div>
        </section>
    </main>
@endsection
