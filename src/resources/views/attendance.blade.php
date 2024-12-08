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
                <div class="status">
                    @if ($workingStatus === $STATUS_BEFORE)
                        勤務外
                    @elseif ($workingStatus === $STATUS_WORKING)
                        出勤中
                    @elseif ($workingStatus === $STATUS_BREAK)
                        休憩中
                    @elseif ($workingStatus === $STATUS_FINISHED)
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
                @if ($workingStatus === $STATUS_BEFORE)
                    <button type="submit" class="start-btn" name="start_work" value="{{ $STATUS_WORKING }}">出勤</button>
                @elseif ($workingStatus === $STATUS_WORKING)
                    <button type="submit" class="end-btn" name="end_work" value="{{ $STATUS_FINISHED }}">退勤</button>
                    <button type="submit" class="start-break-btn" name="start_break" value="{{ $STATUS_BREAK }}">休憩入</button>
                @elseif ($workingStatus === $STATUS_BREAK)
                    <button type="submit" class="end-break-btn" name="end_break" value="{{ $STATUS_WORKING }}">休憩戻</button>
                @elseif ($workingStatus === $STATUS_FINISHED)
                    <p class="message">お疲れ様でした。</p>
                @endif
            </form>
        </section>
    </main>
@endsection
