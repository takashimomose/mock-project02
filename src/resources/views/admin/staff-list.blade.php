@extends('layouts.app')

@section('title', 'スタッフ一覧')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <section class="staff-list-section">
            <h1>スタッフ一覧</h1>
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffMembers as $staffMember)
                        <tr>
                            <td> {{ $staffMember->name }} </td>
                            <td> {{ $staffMember->email }} </td>
                            <td><a href="{{ route('admin.staff.detail', ['id' => $staffMember->id]) }}" class="details-link">詳細</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
@endsection
