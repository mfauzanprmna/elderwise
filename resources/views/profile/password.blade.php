@extends('layouts.app')
@section('content')
    <div class="max-w-3xl mx-auto px-5 md:px-8 py-10 md:py-14">
        <div class="eyebrow">Akun Saya</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-2">Ganti Kata Sandi</h1>
        <p class="text-muted text-lg m-0">Gunakan minimal 8 karakter. Pilih yang mudah Anda ingat namun sulit ditebak orang lain.</p>

        <nav class="ew-tabs" aria-label="Pengaturan akun">
            <a href="{{ route('profile.edit') }}">Profil</a>
            <a href="{{ route('profile.password') }}" aria-current="page">Ganti Kata Sandi</a>
        </nav>

        <div class="card p-8 md:p-10 mt-6">
            <form method="POST" action="{{ route('profile.password.update') }}" class="grid gap-6">
                @csrf

                <div>
                    <label class="form-label" for="current_password">Kata sandi saat ini</label>
                    <input id="current_password" class="form-input" type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password')<div class="text-red text-sm font-semibold mt-1" role="alert">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="password">Kata sandi baru</label>
                    <input id="password" class="form-input" type="password" name="password" required minlength="8" autocomplete="new-password">
                    @error('password')<div class="text-red text-sm font-semibold mt-1" role="alert">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="password_confirmation">Ulangi kata sandi baru</label>
                    <input id="password_confirmation" class="form-input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>

                <button class="btn btn-primary" data-loading-text="Menyimpan…">Simpan Kata Sandi Baru</button>
            </form>

            <p class="ew-hint" style="margin-top:1.5rem">
                Mendaftar dengan Google dan belum punya kata sandi? Keluar lebih dulu, lalu pilih
                <b>Lupa kata sandi</b> di halaman masuk untuk membuat kata sandi pertama Anda.
            </p>
        </div>
    </div>
@endsection
