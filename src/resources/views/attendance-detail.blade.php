@extends('layouts.app')

@section('title', '勤怠詳細')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endpush

@section('content')

    <main class="wrapper">
        <section class="attendance-detail-section">
            <h1>勤怠詳細</h1>
            <table class="attendance-detail-table">
                <tr>
                    <th>名前</th>
                    <td colspan="3">百瀬 尭</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td><input class="form-input" type="text" name="reason" value=""></td>
                    <td></td>
                    <td><input class="form-input" type="text" name="reason" value="">
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td><input class="form-input" type="text" name="reason" value="">
                    </td>
                    <td>～</td>
                    <td><input class="form-input" type="text" name="reason" value="">
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td><input class="form-input" type="text" name="reason" value="">
                    </td>
                    <td>～</td>
                    <td><input class="form-input" type="text" name="reason" value="">
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="3">
                        <textarea class="form-input-reason" type="text" name="reason" value=""></textarea>
                </tr>
            </table>
            <button class="edit-btn">修正</button>
            @error('price')
                <div class="error-message"></div>
            @enderror
        </section>
    </main>
@endsection
