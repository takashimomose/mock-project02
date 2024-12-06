@extends('layouts.app')

@section('title', '勤怠登録')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <section class="attendance-section">
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="container">
                    <div class="status">
                        @if ($workingStatus === 1)
                            勤務外
                        @elseif ($workingStatus === 2)
                            出勤中
                        @elseif ($workingStatus === 3)
                            休憩中
                        @elseif ($workingStatus === 4)
                            退勤済
                        @endif
                    </div>
                    <div class="date">
                        {{ \Carbon\Carbon::now()->isoFormat('Y年M月D日') .
                            '(' .
                            ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::now()->dayOfWeek] .
                            ')' }}
                    </div>
                    <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>
                    @if ($workingStatus === 1)
                        <button type="submit" class="start-btn" name="start_work" value="2">出勤</button>
                    @elseif ($workingStatus === 2)
                        <button type="submit" class="end-btn" name="end_work" value="4">退勤</button>
                        <button type="submit" class="start-break-btn" name="start_break" value="3">休憩入</button>
                    @elseif ($workingStatus === 3)
                        <button type="submit" class="end-break-btn" name="end_break" value="2">休憩戻</button>
                    @elseif ($workingStatus === 4)
                        <p class="message">お疲れ様でした。</p>
                    @endif
                </div>
            </form>
        </section>
    </main>
@endsection
