@extends('layouts.app')
@section('content')
<div class="dashboard-shell"><div class="dashboard-head"><div><div class="eyebrow">YOUR LEARNING JOURNEY</div><h1>Halo, {{ auth()->user()->name }} 👋</h1><p>Belajar tanpa terburu-buru. Setiap langkah kecil tetap berarti.</p></div><div class="overall"><b>{{ $done }}/{{ $total }}</b><span>lesson selesai</span></div></div><div class="module-grid">@foreach($modules as $m)<article class="module-card"><div class="module-top"><span class="module-num">{{ str_pad($m->number,2,'0',STR_PAD_LEFT) }}</span><span class="circle-progress" data-value="{{ $m->lessons->count()?round($m->lessons->whereIn('id',$completed)->count()/$m->lessons->count()*100):0 }}"></span></div><div class="module-body"><div class="eyebrow">MODUL {{ $m->number }}</div><h2>{{ $m->title }}</h2><h3>“{{ $m->slogan }}”</h3><p>{{ $m->description }}</p><div class="module-meta"><span>{{ $m->lessons->count() }} lessons</span><span>{{ $m->badge }}</span></div></div><a class="module-link" href="{{ route('learning.show',$m) }}">{{ $m->lessons->whereIn('id',$completed)->count()? 'Lanjutkan':'Mulai Modul' }} →</a></article>@endforeach</div>

@if($allCompleted)
<div class="certificate-banner">
    <div class="certificate-banner-icon">🎓</div>
    <div class="certificate-banner-text">
        <h2>Selamat, semua modul telah selesai!</h2>
        <p>Bapak/Ibu berhak mendapatkan sertifikat kelulusan ElderWiSE. Unduh sekarang sebagai bukti pencapaian.</p>
    </div>
    <div class="certificate-banner-action">
        @if($certificate)
            <a class="btn btn-primary" href="{{ route('certificate.download') }}">Unduh Sertifikat (PDF)</a>
        @else
            <form method="POST" action="{{ route('certificate.store') }}">
                @csrf
                <button class="btn btn-primary">Buat Sertifikat Saya</button>
            </form>
        @endif
    </div>
</div>
@endif
</div>
@endsection
