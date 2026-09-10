<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · Novastra</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 1.25rem; background: #f7f3e9; color: #18251d; font-family: system-ui, sans-serif; }
        main { width: 100%; max-width: 36rem; padding: 3rem 2rem; border-radius: 1.5rem; background: #fff; text-align: center; box-shadow: 0 1rem 3rem rgb(24 37 29 / 12%); }
        .code { color: #386641; font-size: .875rem; font-weight: 800; letter-spacing: .2em; }
        h1 { margin: 1rem 0 0; font-family: Georgia, serif; font-size: clamp(2rem, 8vw, 2.75rem); }
        .message { max-width: 28rem; margin: 1rem auto 0; color: #5f6962; line-height: 1.75; }
        a { display: inline-block; margin-top: 2rem; border-radius: 999px; padding: .8rem 1.25rem; background: #386641; color: #fff; font-weight: 700; text-decoration: none; }
        a:hover { background: #274c31; }
    </style>
</head>
<body>
    <main>
        <p class="code">ERROR @yield('code')</p>
        <h1>@yield('title')</h1>
        <p class="message">@yield('message')</p>
        <a href="{{ route('home') }}">Kembali ke beranda</a>
    </main>
</body>
</html>
