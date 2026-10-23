@extends('layouts.app')
@section('content')
    <div class="min-h-[calc(100vh-84px)] grid place-items-center px-5 py-12">
        <div class="card w-full max-w-md p-9 md:p-11">
            <div class="eyebrow">Lupa Kata Sandi</div>
            <h1 class="text-3xl mt-2 mb-2">Atur ulang kata sandi</h1>
            <p class="text-muted text-lg">Tenang, hal ini wajar terjadi. Masukkan email yang Anda pakai saat mendaftar. Kami akan memeriksanya, lalu mengirim tautan untuk membuat kata sandi baru.</p>

            <form method="POST" action="{{ route('password.email') }}" class="grid gap-5 mt-7">
                @csrf
                <label class="form-label">
                    Email
                    <input class="form-input" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="nama@kampus.ac.id">
                </label>
                @error('email')<div class="text-red text-sm font-semibold" role="alert">{{ $message }}</div>@enderror

                <button class="btn btn-primary w-full" data-loading-text="Memeriksa email…">Kirim Tautan</button>
            </form>

            <p class="text-muted text-base mt-6 mb-0">
                Mendaftar dengan Google? Anda tetap dapat membuat kata sandi di sini, atau cukup masuk dengan tombol Google.
            </p>

            <p class="text-center text-muted mt-6 mb-0 text-lg">
                <a class="text-blue-dark font-bold" href="{{ route('login') }}">Kembali ke halaman masuk</a>
            </p>
        </div>
    </div>
@endsection
