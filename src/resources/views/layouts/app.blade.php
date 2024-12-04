<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', '')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100..900&display=swap" rel="stylesheet">
    @stack('css')
</head>

<body>
    <header class="header">
        <div class="header-wrapper">
            <h1 class="header-logo">
                <a href="">
                    <picture>
                        <source media="(max-width: 1200px)" srcset="{{ asset('images/logo-tablet.svg') }}">
                        <img src="{{ asset('images/logo.svg') }}" class="responsive-logo" alt="site logo">
                    </picture>
                </a>
            </h1>
            <nav class="header-nav">
                <ul class="header-nav-list">
                    @if (Auth::check())
                        <!-- ログインしている場合 -->
                        <li class="header-nav-item">
                            <form class="header-nav-logout" action="{{ route('authentication.destroy') }}" method="post">
                                @csrf
                                <button type="submit">ログアウト</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </header>
    @yield('content') <!-- ここに各ページのコンテンツが挿入されます -->
    <footer class="footer"></footer>
</body>

</html>
