<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @if(filled($site->description))
        <meta name="description" content="{{ $site->description }}">
    @endif
    <title>{{ $title ?? $site->site_name }}</title>
    @if($site->faviconUrl() ?? $site->logoUrl())
        <link rel="icon" href="{{ $site->faviconUrl() ?? $site->logoUrl() }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.ui-head')
    @stack('head')
</head>
<body>
    <a href="#konten-utama" class="skip-link">Langsung ke konten</a>

    <nav class="sticky top-0 z-50 h-20 md:h-[84px] bg-white border-b-2 border-line flex items-center justify-between gap-4 px-5 md:px-[5vw]">
        <a class="no-underline text-xl md:text-2xl min-w-0" href="{{ route('home') }}">
            <x-brand :site="$site" />
        </a>
        <div class="ew-nav-actions">
            @auth
                <a class="btn btn-ghost btn-sm" href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                @if(auth()->user()->role === 'admin')
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                @include('partials.profile-menu')
            @else
                <a class="text-navy no-underline font-bold text-base md:text-lg hover:text-blue" href="{{ route('login') }}">Masuk</a>
                <a class="btn btn-primary btn-sm" href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
    </nav>

    @include('partials.flash')

    <main id="konten-utama">
        @yield('content')
    </main>

    @if((filled($site->footer_text) || $sitePartners->isNotEmpty()) && ! View::hasSection('hide_footer'))
        <footer class="bg-white border-t-2 border-line px-5 py-8 text-center">
            @if($sitePartners->isNotEmpty())
                <p class="text-sm font-extrabold tracking-wider text-muted uppercase mb-4">Diselenggarakan oleh</p>
                <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4 mb-6">
                    @foreach($sitePartners as $partner)
                        @if($partner->url)<a href="{{ $partner->url }}" target="_blank" rel="noopener noreferrer" title="{{ $partner->name }}">@endif
                        <img src="{{ $partner->logoUrl() }}" alt="{{ $partner->name }}" class="h-16 md:h-24 w-auto max-w-[260px] object-contain">
                        @if($partner->url)</a>@endif
                    @endforeach
                </div>
            @endif
            @if(filled($site->footer_text))
                <p class="text-muted text-base m-0 max-w-3xl mx-auto">{{ $site->footer_text }}</p>
            @endif
        </footer>
    @endif

    @include('partials.ui-scripts')
    @stack('scripts')
</body>
</html>
