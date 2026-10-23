@extends('layouts.app')
@section('content')

    {{-- ===================== HERO ===================== --}}
    <section class="max-w-7xl mx-auto overflow-x-clip grid md:grid-cols-2 items-center gap-12 px-5 md:px-10 py-14 md:py-20">
        <div>
            @if(filled($site->tagline))
                <div class="eyebrow">{{ $site->tagline }}</div>
            @endif
            <h1 class="text-4xl md:text-5xl leading-tight my-4">
                Belajar teknologi dengan tenang.<br>
                <span class="text-blue">Learn · Explore · Practice · Grow.</span>
            </h1>
            <p class="text-xl text-ink max-w-xl leading-relaxed">
                {{ $site->description ?: $site->site_name . ' membantu guru dan pendidik dewasa membangun kepercayaan diri digital melalui langkah kecil, praktik nyata, dan perjalanan belajar yang aman.' }}
            </p>
            <div class="flex flex-wrap gap-4 my-8">
                <a class="btn btn-primary" href="{{ auth()->check() ? route('dashboard') : route('register') }}">
                    Mulai Perjalanan <span>→</span>
                </a>
                <a class="btn btn-ghost" href="#principles">Lihat cara belajar</a>
            </div>
            @php $jp = \App\Models\Module::count() * \App\Models\Module::TRAINING_HOURS; @endphp
            @if($jp > 0)
                <p class="flex items-center gap-2 text-navy font-bold text-lg mb-5">
                    <span class="text-2xl" aria-hidden="true">🎓</span>
                    Selesaikan semua modul dan dapatkan e-sertifikat {{ $jp }} JP.
                </p>
            @endif
            <p class="text-muted italic text-lg">&ldquo;Saya tidak takut lagi menggunakan teknologi.&rdquo;</p>
        </div>

        <div class="relative flex justify-center md:justify-end">
            {{-- Ornamen dekoratif halus di belakang kartu — dibatasi dalam wrapper supaya tidak keluar/terpotong --}}
            <div class="relative w-full max-w-md">
                <div class="absolute -z-10 w-56 h-56 rounded-full bg-blue-tint/70 -top-8 -right-6" aria-hidden="true"></div>
                <div class="absolute -z-10 w-36 h-36 rounded-full bg-blue-tint/50 -bottom-6 -left-6" aria-hidden="true"></div>

                <div class="card relative p-7">
                    <div class="flex items-center justify-between text-muted text-sm font-extrabold tracking-wide">
                        <span>My Journey</span>
                        <b class="text-navy text-base">0%</b>
                    </div>
                    <div class="h-3 bg-blue-tint/60 rounded-full my-4 overflow-hidden">
                        <div class="h-full bg-blue rounded-full" style="width:0%"></div>
                    </div>

                    @php
                        $levels = [
                            ['num' => '01', 'title' => 'Getting Started', 'subtitle' => 'I Can Do It!', 'active' => true],
                            ['num' => '02', 'title' => 'Digital Essentials', 'subtitle' => 'Send, Share & Collaborate', 'active' => false],
                            ['num' => '03', 'title' => 'Digital Detective', 'subtitle' => 'Smart Digital Information', 'active' => false],
                        ];
                    @endphp
                    @foreach($levels as $level)
                        <div class="flex items-center gap-4 py-4 border-t border-line first:border-t-0">
                            <span class="w-11 h-11 rounded-full grid place-items-center font-extrabold text-sm shrink-0 {{ $level['active'] ? 'bg-blue text-white' : 'bg-blue-tint text-blue-dark' }}">
                                {{ $level['num'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <b class="block text-navy text-[17px] leading-snug">{{ $level['title'] }}</b>
                                <small class="block text-muted text-[15px] leading-snug">{{ $level['subtitle'] }}</small>
                            </div>
                            <em class="not-italic text-lg text-muted shrink-0">{{ $level['active'] ? '→' : '🔒' }}</em>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== PRINSIP BELAJAR ===================== --}}
    <section id="principles" class="bg-white py-16 md:py-24 px-5 md:px-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-wrap justify-between items-end gap-8 mb-10">
                <div>
                    <div class="eyebrow">Desain Belajar</div>
                    <h2 class="text-3xl md:text-4xl mt-2">Pelan-pelan, satu langkah setiap kali.</h2>
                </div>
                <p class="text-lg text-ink max-w-md leading-relaxed">
                    Dirancang untuk peserta basic digital literacy dengan huruf besar, instruksi singkat,
                    dan latihan yang dekat dengan pekerjaan sehari-hari.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @php
                    $principles = [
                        ['num' => '01', 'title' => 'Tidak Takut Salah', 'desc' => 'Setiap kesalahan adalah bagian dari proses belajar.'],
                        ['num' => '02', 'title' => 'Belajar Sambil Praktik', 'desc' => 'Belajar langsung melalui latihan nyata.'],
                        ['num' => '03', 'title' => 'Langkah Kecil', 'desc' => 'Kemampuan dibangun sedikit demi sedikit.'],
                        ['num' => '04', 'title' => 'Menghargai Proses', 'desc' => 'Yang penting maju, bukan sempurna.'],
                    ];
                @endphp
                @foreach($principles as $p)
                    <article class="card p-6">
                        <span class="text-blue-dark font-extrabold text-sm">Prinsip {{ $p['num'] }}</span>
                        <h3 class="text-xl mt-4 mb-1.5">{{ $p['title'] }}</h3>
                        <p class="text-ink text-base m-0">{{ $p['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
