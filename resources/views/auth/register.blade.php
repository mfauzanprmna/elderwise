@extends('layouts.app')
@section('content')
    <div class="min-h-[calc(100vh-84px)] grid place-items-center px-5 py-12">
        <div class="card w-full max-w-md p-9 md:p-11">
            <div class="eyebrow">Start Your Journey</div>
            <h1 class="text-3xl mt-2 mb-2">Buat akun {{ $site->site_name }}</h1>
            <p class="text-muted text-lg">Tidak perlu sempurna. Mulai dari satu langkah.</p>

            <div class="mt-7">
                <x-google-auth label="Daftar dengan Google" />
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="grid gap-5">
                @csrf
                <label class="form-label">
                    Nama
                    <input class="form-input" name="name" value="{{ old('name') }}" required autocomplete="name">
                </label>
                @error('name')<div class="text-red text-sm font-semibold">{{ $message }}</div>@enderror

                <label class="form-label">
                    Email
                    <input class="form-input" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                </label>
                @error('email')<div class="text-red text-sm font-semibold">{{ $message }}</div>@enderror

                <label class="form-label">
                    Kata sandi
                    <input class="form-input" name="password" type="password" required autocomplete="new-password">
                </label>

                <label class="form-label">
                    Ulangi kata sandi
                    <input class="form-input" name="password_confirmation" type="password" required autocomplete="new-password">
                </label>
                @error('password')<div class="text-red text-sm font-semibold">{{ $message }}</div>@enderror

                <button class="btn btn-primary w-full">Buat Akun</button>
            </form>

            <p class="text-center text-muted mt-6 mb-0 text-lg">
                Sudah punya akun?
                <a class="text-blue-dark font-bold" href="{{ route('login') }}">Masuk</a>
            </p>
        </div>
    </div>
@endsection
