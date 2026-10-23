@extends('layouts.app')
@section('content')
    <div class="min-h-[calc(100vh-84px)] grid place-items-center px-5 py-12">
        <div class="card w-full max-w-md p-9 md:p-11">
            <div class="eyebrow">Welcome Back</div>
            <h1 class="text-3xl mt-2 mb-2">Masuk ke {{ $site->site_name }}</h1>
            <p class="text-muted text-lg">Lanjutkan perjalanan digital Anda.</p>

            <div class="mt-7">
                <x-google-auth label="Masuk dengan Google" />
            </div>

            <form method="POST" action="{{ route('login.store') }}" class="grid gap-5">
                @csrf
                <label class="form-label">
                    Email
                    <input class="form-input" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </label>
                @error('email')<div class="text-red text-sm font-semibold">{{ $message }}</div>@enderror

                <label class="form-label">
                    Kata sandi
                    <input class="form-input" name="password" type="password" required autocomplete="current-password">
                </label>

                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <label class="flex items-center gap-2.5 font-semibold text-lg">
                        <input type="checkbox" name="remember" class="w-5.5 h-5.5">
                        Ingat saya
                    </label>
                    <a class="text-blue-dark font-bold text-lg underline" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                </div>

                <button class="btn btn-primary w-full" data-loading-text="Sedang masuk…">Masuk</button>
            </form>

            <p class="text-center text-muted mt-6 mb-0 text-lg">
                Belum punya akun?
                <a class="text-blue-dark font-bold" href="{{ route('register') }}">Daftar sekarang</a>
            </p>
        </div>
    </div>
@endsection
