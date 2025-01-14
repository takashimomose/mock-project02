@extends('layouts.app')

@section('title', 'メールアドレス認証')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <div class="container">
            <h1>メールアドレスの確認</h1>
            <p>登録したメールアドレスに確認リンクを送信しました。メールを確認し、リンクをクリックして登録を完了してください。</p>
        </div>
    </main>
@endsection
