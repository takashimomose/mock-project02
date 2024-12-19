@extends('layouts.app')

@section('title', '管理者ログイン')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endpush

@section('content')
    <main class="wrapper">
        <section class="login-section">
            <h1>管理者ログイン</h1>

            <form method="POST" action="{{ route('admin.auth.store') }}">
                @csrf
                <div class="form-group">
                    <label for="email" class="form-label">メールアドレス</label>
                    <input class="form-input" type="email" name="email"
                        value="{{ old('email') }}"placeholder="例: test@example.com">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">パスワード</label>
                    <input class="form-input" type="password" name="password" placeholder="例: coachtech1106">
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="primary-btn">
                        管理者ログインする
                    </button>
                </div>
            </form>
        </section>
    </main>
@endsection
