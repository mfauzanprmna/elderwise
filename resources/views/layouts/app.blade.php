<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $title ?? 'ElderWiSE' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/elderwise.css"></head><body>
<a href="#konten-utama" class="skip-link">Langsung ke konten</a>
<nav class="navbar"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">E</span><span>Elder<span>WiSE</span></span></a><div class="nav-links">@auth<a href="{{ route('dashboard') }}">Dashboard</a>@if(auth()->user()->role==='admin')<a href="{{ route('admin.dashboard') }}">Admin</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-button">Keluar</button></form>@else<a href="{{ route('login') }}">Masuk</a><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>@endauth</div></nav>
@if(session('success'))<div class="toast success">✓ {{ session('success') }}</div>@endif
<main id="konten-utama">@yield('content')</main><script src="/js/elderwise.js"></script>@stack('scripts')</body></html>
