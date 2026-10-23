@php
    $ewUser = auth()->user();
    $ewAvatar = $ewUser->avatarUrl();
@endphp
<details class="ew-profile">
    <summary class="ew-profile-btn" aria-label="Menu profil {{ $ewUser->name }}">
        <span class="ew-avatar" aria-hidden="true">
            {{ $ewUser->initials() }}
            @if($ewAvatar)<img src="{{ $ewAvatar }}" alt="" referrerpolicy="no-referrer" loading="lazy" onerror="this.remove()">@endif
        </span>
        <span class="ew-profile-name">{{ $ewUser->name }}</span>
        <svg class="ew-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7.5l5 5 5-5"/></svg>
    </summary>

    <div class="ew-menu">
        <div class="ew-menu-head">
            <b>{{ $ewUser->name }}</b>
            <span>{{ $ewUser->email }}</span>
        </div>

        <a href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.edit')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            Pengaturan Akun
        </a>
        <a href="{{ route('profile.password') }}" @if(request()->routeIs('profile.password')) aria-current="page" @endif>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
            Ganti Kata Sandi
        </a>

        <hr>

        <form method="POST" action="{{ route('logout') }}" class="m-0"
              onsubmit="return confirm({{ \Illuminate\Support\Js::from('Apakah Anda yakin ingin keluar?') }})">
            @csrf
            <button type="submit" class="ew-menu-danger" data-loading-text="Keluar…">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                Keluar
            </button>
        </form>
    </div>
</details>
