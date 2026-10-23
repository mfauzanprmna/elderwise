@extends('layouts.app')
@section('content')
    <div class="min-h-[calc(100vh-84px)] grid place-items-center px-5 py-12">
        <div class="card w-full max-w-lg p-9 md:p-11">
            <div class="eyebrow">Verifikasi Email</div>
            <h1 class="text-3xl mt-2 mb-2">Satu langkah lagi</h1>
            <p class="text-ink text-lg">
                Kami sudah mengirim email berisi tautan verifikasi ke
                <b class="text-navy break-all">{{ $email }}</b>.
                Buka email tersebut, lalu tekan tombol <b>Verifikasi Email Saya</b>.
            </p>
            <p class="text-muted text-base">
                Tidak menemukannya? Periksa juga folder <b>Spam</b> atau <b>Promosi</b>. Tautan berlaku {{ $menit }} menit.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
                @csrf
                <button class="btn btn-primary w-full" data-loading-text="Mengirim email…">Kirim Ulang Email Verifikasi</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Apakah Anda yakin ingin keluar dan menggunakan email lain?') }})">
                @csrf
                <button class="border-0 bg-transparent text-blue-dark font-bold text-lg underline cursor-pointer">Keluar dan gunakan email lain</button>
            </form>
        </div>
    </div>
@endsection
