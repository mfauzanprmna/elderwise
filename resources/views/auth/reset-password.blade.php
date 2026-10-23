@extends('layouts.app')
@section('content')
    <div class="min-h-[calc(100vh-84px)] grid place-items-center px-5 py-12">
        <div class="card w-full max-w-md p-9 md:p-11">
            <div class="eyebrow">Kata Sandi Baru</div>
            <h1 class="text-3xl mt-2 mb-2">Buat kata sandi baru</h1>
            <p class="text-muted text-lg">Gunakan minimal 8 karakter. Pilih kata sandi yang mudah Anda ingat namun sulit ditebak orang lain.</p>

            <form method="POST" action="{{ route('password.update') }}" class="grid gap-5 mt-7">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label class="form-label">
                    Email
                    <input class="form-input" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" @readonly(filled(old('email', $email)))>
                </label>
                @error('email')<div class="text-red text-sm font-semibold" role="alert">{{ $message }}</div>@enderror

                <label class="form-label">
                    Kata sandi baru
                    <input class="form-input" name="password" type="password" required autofocus autocomplete="new-password">
                </label>

                <label class="form-label">
                    Ulangi kata sandi baru
                    <input class="form-input" name="password_confirmation" type="password" required autocomplete="new-password">
                </label>
                @error('password')<div class="text-red text-sm font-semibold" role="alert">{{ $message }}</div>@enderror

                <button class="btn btn-primary w-full">Simpan Kata Sandi Baru</button>
            </form>

            <p class="text-center text-muted mt-6 mb-0 text-lg">
                <a class="text-blue-dark font-bold" href="{{ route('login') }}">Kembali ke halaman masuk</a>
            </p>
        </div>
    </div>
@endsection
