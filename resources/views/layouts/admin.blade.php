<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    @if(filled($site->description))
        <meta name="description" content="{{ $site->description }}">
    @endif
    <title>{{ $title ?? 'Admin' }} · {{ $site->site_name }}</title>
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
@php
    // Ikon menu (heroicons outline). Setiap item: rute tujuan, pola rute aktif, label, path ikon.
    $icons = [
        'home' => 'M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        'layers' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
        'book' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
        'users' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        'award' => 'M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5',
        'cog' => 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'external' => 'M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25',
        'logout' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75',
        'menu' => 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5',
        'close' => 'M6 18L18 6M6 6l12 12',
        'user' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        'key' => 'M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z',
    ];
    $menu = [
        ['route' => 'admin.dashboard',       'match' => 'admin.dashboard',     'label' => 'Dashboard',         'icon' => 'home'],
        ['route' => 'admin.modules.index',   'match' => 'admin.modules.*',     'label' => 'Modul',             'icon' => 'layers'],
        ['route' => 'admin.lessons.index',   'match' => 'admin.lessons.*',     'label' => 'Materi',            'icon' => 'book'],
        ['route' => 'admin.users.index',     'match' => 'admin.users.*',       'label' => 'Pengguna',          'icon' => 'users'],
        ['route' => 'admin.certificate.edit','match' => 'admin.certificate.*', 'label' => 'Sertifikat',        'icon' => 'award'],
        ['route' => 'admin.settings.edit',   'match' => 'admin.settings.*',    'label' => 'Pengaturan Situs',  'icon' => 'cog'],
    ];
@endphp

<a href="#konten-utama" class="skip-link">Langsung ke konten</a>

<div id="admin-layout" class="admin-layout md:flex min-h-screen">

    {{-- Bar atas khusus layar kecil: hanya merek + tombol menu (di layar lebar diganti sidebar) --}}
    <header class="md:hidden sticky top-0 z-40 h-16 bg-white border-b-2 border-line flex items-center justify-between gap-3 px-4">
        <x-brand :site="$site" :size="36" class="text-lg" />
        <button type="button" id="sidebar-open" aria-label="Buka menu" aria-controls="admin-sidebar" aria-expanded="false"
                class="w-12 h-12 grid place-items-center rounded-xl border-2 border-line-strong bg-white text-navy cursor-pointer">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['menu'] }}"/></svg>
        </button>
    </header>

    <div id="sidebar-backdrop" class="hidden fixed inset-0 z-40 bg-navy/50 md:hidden"></div>

    <aside id="admin-sidebar"
           class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] -translate-x-full transition-transform duration-200 md:translate-x-0 md:sticky md:top-0 md:h-screen md:w-[284px] md:max-w-none md:shrink-0 bg-white border-r-2 border-line flex flex-col">

        <div class="h-20 px-5 border-b-2 border-line flex items-center justify-between gap-2 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand no-underline min-w-0 text-lg">
                <x-brand :site="$site" :size="40" />
            </a>
            <button type="button" id="admin-sidebar-toggle" aria-label="Tutup sidebar" aria-controls="admin-sidebar" aria-expanded="true" title="Tutup sidebar"
                    class="hidden md:grid w-11 h-11 place-items-center rounded-xl border-2 border-line-strong bg-white text-navy cursor-pointer shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['menu'] }}"/></svg>
            </button>
            <button type="button" id="sidebar-close" aria-label="Tutup menu"
                    class="md:hidden w-11 h-11 grid place-items-center rounded-xl border-2 border-line-strong bg-white text-navy cursor-pointer shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['close'] }}"/></svg>
            </button>
        </div>

        <div class="admin-sidebar-label px-5 pt-5 pb-2 text-xs font-extrabold tracking-wider text-muted uppercase">Panel Admin</div>

        <nav class="flex-1 overflow-y-auto px-3 pb-4 grid content-start gap-1" aria-label="Menu admin">
            @foreach($menu as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif
                   title="{{ $item['label'] }}" class="admin-sidebar-link flex items-center gap-3 px-4 py-3 rounded-xl no-underline text-[17px] font-bold
                          {{ $active ? 'bg-blue text-white shadow-[0_6px_16px_rgba(11,87,196,0.25)]' : 'text-navy hover:bg-blue-tint' }}">
                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}"/></svg>
                    <span class="admin-sidebar-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t-2 border-line p-3 grid gap-1 shrink-0">
            @php $adminUser = auth()->user(); $adminAvatar = $adminUser->avatarUrl(); @endphp
            <a href="{{ route('profile.edit') }}" title="{{ $adminUser->email }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl no-underline hover:bg-blue-tint min-w-0">
                <span class="ew-avatar" aria-hidden="true">
                    {{ $adminUser->initials() }}
                    @if($adminAvatar)<img src="{{ $adminAvatar }}" alt="" referrerpolicy="no-referrer" loading="lazy" onerror="this.remove()">@endif
                </span>
                <span class="admin-sidebar-label min-w-0 text-sm text-muted">Masuk sebagai
                    <b class="block text-navy text-base truncate">{{ $adminUser->name }}</b>
                </span>
            </a>
            <a href="{{ route('profile.edit') }}" title="Pengaturan Akun" @if(request()->routeIs('profile.edit')) aria-current="page" @endif
               class="flex items-center gap-3 px-4 py-3 rounded-xl no-underline text-[16px] font-bold text-navy hover:bg-blue-tint">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['user'] }}"/></svg>
                <span class="admin-sidebar-label">Pengaturan Akun</span>
            </a>
            <a href="{{ route('profile.password') }}" title="Ganti Kata Sandi" @if(request()->routeIs('profile.password')) aria-current="page" @endif
               class="flex items-center gap-3 px-4 py-3 rounded-xl no-underline text-[16px] font-bold text-navy hover:bg-blue-tint">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['key'] }}"/></svg>
                <span class="admin-sidebar-label">Ganti Kata Sandi</span>
            </a>
            <a href="{{ route('dashboard') }}" title="Tampilan peserta" class="flex items-center gap-3 px-4 py-3 rounded-xl no-underline text-[16px] font-bold text-navy hover:bg-blue-tint">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['external'] }}"/></svg>
                <span class="admin-sidebar-label">Tampilan peserta</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Apakah Anda yakin ingin keluar?') }})">
                @csrf
                <button title="Keluar" data-loading-text="Keluar…" class="admin-sidebar-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-[16px] font-bold text-red bg-transparent border-0 cursor-pointer hover:bg-red-tint text-left">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['logout'] }}"/></svg>
                    <span class="admin-sidebar-label">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <main id="konten-utama" class="flex-1 min-w-0">
        @include('partials.flash')
        @yield('content')
    </main>
</div>

<script>
(function () {
    var sidebar = document.getElementById('admin-sidebar');
    var layout = document.getElementById('admin-layout');
    var backdrop = document.getElementById('sidebar-backdrop');
    var openBtn = document.getElementById('sidebar-open');
    var desktopToggle = document.getElementById('admin-sidebar-toggle');
    var desktopQuery = window.matchMedia('(min-width: 768px)');

    function setCollapsed(collapsed, persist) {
        layout.classList.toggle('is-sidebar-collapsed', collapsed);
        desktopToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        desktopToggle.setAttribute('aria-label', collapsed ? 'Buka sidebar' : 'Tutup sidebar');
        desktopToggle.setAttribute('title', collapsed ? 'Buka sidebar' : 'Tutup sidebar');
        if (persist) {
            try {
                window.localStorage.setItem('ew-admin-sidebar', collapsed ? 'closed' : 'open');
            } catch (e) {
                // Preferensi tidak tersedia di browser privat.
            }
        }
    }

    function setOpen(open) {
        sidebar.classList.toggle('-translate-x-full', !open);
        backdrop.classList.toggle('hidden', !open);
        openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    try {
        setCollapsed(window.localStorage.getItem('ew-admin-sidebar') === 'closed', false);
    } catch (e) {
        setCollapsed(false, false);
    }

    openBtn.addEventListener('click', function () { setOpen(true); });
    document.getElementById('sidebar-close').addEventListener('click', function () { setOpen(false); });
    backdrop.addEventListener('click', function () { setOpen(false); });
    desktopToggle.addEventListener('click', function () {
        setCollapsed(!layout.classList.contains('is-sidebar-collapsed'), true);
    });
    desktopQuery.addEventListener('change', function (event) {
        if (event.matches) setOpen(false);
    });
    sidebar.querySelectorAll('nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!desktopQuery.matches) setOpen(false);
        });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
})();
</script>
@include('partials.ui-scripts')
@stack('scripts')
</body>
</html>
