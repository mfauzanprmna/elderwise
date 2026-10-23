@extends('layouts.app')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/learning.css') }}?v={{ @filemtime(public_path('css/learning.css')) }}">
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-10 md:py-14">

        {{-- ===================== HEADER: RANK & PROGRESS ===================== --}}
        <div class="rounded-3xl p-7 md:p-9 mb-10 text-white relative overflow-hidden"
             style="background:linear-gradient(135deg, var(--color-navy) 0%, var(--color-blue-dark) 55%, var(--color-blue) 100%)">

            {{-- lingkaran dekoratif halus, dikunci di belakang konten --}}
            <div class="absolute w-72 h-72 rounded-full bg-white/5 -right-16 -top-20 pointer-events-none"></div>
            <div class="absolute w-44 h-44 rounded-full bg-white/5 -left-10 -bottom-16 pointer-events-none"></div>

            <div class="relative flex flex-wrap items-center gap-7">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-white/10 border-2 border-white/25 grid place-items-center text-4xl md:text-5xl shrink-0">
                    {{ $rank['icon'] }}
                </div>

                <div class="flex-1 min-w-[260px]">
                    <p class="text-blue-100 text-base m-0" style="color:#c3d8f5">Selamat datang kembali,</p>
                    <h1 class="text-white text-2xl md:text-3xl my-1">{{ auth()->user()->name }}</h1>
                    <p class="m-0 text-lg font-bold" style="color:#ffd97a">{{ $rank['name'] }}</p>
                </div>

                <div class="w-full md:w-auto md:min-w-[320px]">
                    <div class="flex flex-wrap justify-between gap-x-3 gap-y-0.5 text-sm font-semibold mb-1.5" style="color:#c3d8f5">
                        <span class="whitespace-nowrap">{{ $totalXp }} XP</span>
                        <span class="whitespace-nowrap">{{ $rank['next_name'] ? $rank['next_name'] . ' pada ' . $rank['next_min'] . ' XP' : 'Rank tertinggi!' }}</span>
                    </div>
                    <div class="h-3.5 rounded-full bg-white/15 overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ $rank['progress_pct'] }}%; background:linear-gradient(90deg, #ffd97a, #ffb64c)"></div>
                    </div>
                </div>
            </div>

            <div class="relative flex flex-wrap gap-4 md:gap-8 mt-7 pt-6 border-t border-white/15">
                <div>
                    <b class="block text-2xl md:text-3xl leading-tight">{{ $done }}/{{ $total }}</b>
                    <span class="text-sm" style="color:#c3d8f5">Materi selesai</span>
                </div>
                <div>
                    <b class="block text-2xl md:text-3xl leading-tight">{{ $modulesCompletedCount }}/{{ $modules->count() }}</b>
                    <span class="text-sm" style="color:#c3d8f5">Modul tuntas</span>
                </div>
                <div>
                    <b class="block text-2xl md:text-3xl leading-tight">{{ $totalXp }}</b>
                    <span class="text-sm" style="color:#c3d8f5">Total XP</span>
                </div>
            </div>
        </div>

        {{-- ===================== KOLEKSI LENCANA ===================== --}}
        <div class="mb-10">
            <h2 class="text-2xl mb-4">Koleksi Lencana</h2>
            <div class="flex flex-wrap gap-4">
                @foreach($modules as $m)
                    @php
                        $isModuleDone = $path->isModuleComplete($m);
                    @endphp
                    <div class="flex flex-col items-center gap-2 w-24 text-center" title="{{ $m->badge }}">
                        <x-badge-medal :module="$m" :state="$isModuleDone ? 'done' : 'locked'" :size="64" />
                        <span class="text-xs font-semibold leading-tight {{ $isModuleDone ? 'text-navy' : 'text-muted' }}">
                            {{ $m->badge }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================== PETA MODUL ===================== --}}
        <h2 class="text-2xl mb-4">Peta Belajar</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($modules as $m)
                @php
                    $moduleDone = $path->moduleCompletedCount($m);
                    $modulePct = $path->modulePercent($m);
                    $isModuleDone = $path->isModuleComplete($m);
                    $isStarted = $moduleDone > 0;
                    $isModuleUnlocked = $path->isModuleUnlocked($m);
                    $prevModule = $loop->first ? null : $modules[$loop->index - 1];
                @endphp
                <article class="card p-6 flex flex-col relative overflow-hidden {{ $isModuleDone ? 'ring-2' : '' }}"
                          style="{{ $isModuleDone ? 'box-shadow:0 10px 28px rgba(240,180,41,.18); border-color:#f0d896;' : '' }}">

                    @if($isModuleDone)
                        <span class="absolute top-0 right-0 text-xs font-extrabold text-white px-3 py-1.5 rounded-bl-xl"
                              style="background:linear-gradient(135deg, #f0b429, #d98e04)">✓ TUNTAS</span>
                    @endif

                    <div class="flex items-center gap-4">
                        <x-badge-medal :module="$m" :state="$isModuleDone ? 'done' : ($isStarted ? 'progress' : 'locked')" :size="56" />
                        <div class="flex-1">
                            <span class="text-muted text-sm font-bold">Modul {{ $m->number }}</span>
                            <div class="h-2 rounded-full bg-blue-tint/60 overflow-hidden mt-1.5">
                                <div class="h-full rounded-full" style="width:{{ $modulePct }}%; background:{{ $isModuleDone ? 'linear-gradient(90deg,' . $m->badgeColor()['to'] . ',' . $m->badgeColor()['border'] . ')' : 'var(--color-blue)' }}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 py-4">
                        <h2 class="text-2xl leading-snug mt-1 mb-1">{{ $m->title }}</h2>
                        <h3 class="text-blue-dark font-bold text-base mb-3">&ldquo;{{ $m->slogan }}&rdquo;</h3>
                        <p class="text-ink text-base min-h-[4rem]">{{ $m->description }}</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="bg-blue-tint text-navy text-sm font-semibold rounded-full px-3 py-1.5">{{ $m->lessons->count() }} materi</span>
                            @unless($isModuleUnlocked)
                                <span class="bg-amber-tint text-amber text-sm font-semibold rounded-full px-3 py-1.5"><x-icon-lock /> Terkunci</span>
                            @endunless
                        </div>
                    </div>

                    @if($isModuleUnlocked)
                        <a class="btn w-full {{ $isModuleDone ? 'btn-ghost' : 'btn-primary' }}" href="{{ route('learning.show', $m) }}">
                            {{ $isModuleDone ? 'Lihat Kembali' : ($isStarted ? 'Lanjutkan' : 'Mulai Modul') }} →
                        </a>
                    @else
                        <span class="btn btn-ghost w-full is-disabled" aria-disabled="true">
                            <x-icon-lock /> Selesaikan {{ $prevModule ? 'Modul ' . $prevModule->number : 'modul sebelumnya' }} dulu
                        </span>
                    @endif
                </article>
            @endforeach
        </div>

        {{-- ===================== BANNER SERTIFIKAT ===================== --}}
        @if($allCompleted)
            <div class="mt-10 flex flex-wrap items-center gap-6 rounded-2xl p-7 md:p-8 text-white shadow-elder-lg relative overflow-hidden"
                 style="background:linear-gradient(135deg, #8a5d00, #f0b429)">
                <div class="text-5xl leading-none">🎓</div>
                <div class="flex-1 min-w-[260px]">
                    <h2 class="text-white text-2xl mb-1.5">Selamat, semua modul telah selesai!</h2>
                    <p class="m-0 text-lg" style="color:#fff3d6">
                        Bapak/Ibu berhak mendapatkan sertifikat kelulusan {{ $site->site_name }}. File PDF berisi 2 halaman: sertifikat dan rincian materi dengan total {{ $modules->count() * \App\Models\Module::TRAINING_HOURS }} JP (Jam Pelatihan).
                    </p>
                </div>
                <div>
                    @if($certificate && $certificate->isApproved())
                        <a class="btn bg-white text-amber hover:bg-amber-tint" href="{{ route('certificate.download') }}" data-loading-max="7000" data-loading-text="Menyiapkan sertifikat…">
                            Unduh Sertifikat (PDF, 2 halaman)
                        </a>
                    @elseif($certificate)
                        <div class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold text-white/90">
                            Pengajuan sertifikat sudah terkirim otomatis dan sedang menunggu persetujuan admin.
                        </div>
                    @elseif($certificateRequestPending)
                        <div class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold text-white/90">
                            Pengajuan sertifikat sedang tertunda. Muat ulang Dashboard untuk mencoba lagi.
                        </div>
                    @else
                        <div class="rounded-xl bg-white/15 px-4 py-3 text-sm font-semibold text-white/90">
                            Pengajuan sertifikat akan dikirim otomatis setelah semua modul selesai.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
