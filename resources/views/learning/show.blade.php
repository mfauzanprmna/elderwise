@extends('layouts.app')
@section('hide_footer', '1')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/learning.css') }}?v={{ @filemtime(public_path('css/learning.css')) }}">
@endpush

@php
    $celebrate = session('module_completed') ?? $moduleCompleted;
    $overallPct = $path->overallPercent();

    $nextLabel = ($nextModule && $nextModule->id !== $module->id)
        ? 'Lanjut ke Modul ' . $nextModule->number
        : 'Materi Berikutnya';

    // Status kemajuan materi ini: 0% sampai materi digulir habis (atau latihan lulus), lalu langsung 100%.
    $statusState = $completedThis ? 'done' : ($lesson && $lesson->hasQuiz() && $readingDone && $lesson->hasReadableContent() ? 'quiz' : 'todo');
    $statusText = ['done' => '100% · Materi selesai', 'quiz' => 'Tinggal mengerjakan latihan', 'todo' => '0% · Belum selesai'][$statusState];

    $config = [
        'celebrate' => $celebrate,
        'lessonId' => $lesson?->id,
        'moduleId' => $module->id,
        'finishUrl' => $lesson ? route('learning.finish', $lesson) : null,
        'csrf' => csrf_token(),
        'needsReading' => $needsReading,
        'completed' => $completedThis,
        'hasQuiz' => $lesson ? $lesson->hasQuiz() : false,
    ];
@endphp

@section('content')
    <div id="learn-grid" class="ew-learn grid grid-cols-1 md:grid-cols-[360px_1fr] h-[calc(100vh-84px)] md:h-[calc(100vh-84px)] overflow-hidden">
        {{-- Pulihkan pilihan terakhir (menu tertutup/terbuka) sebelum halaman digambar, supaya tidak berkedip. --}}
        <script>try { if (localStorage.getItem('ew-learn-menu') === 'closed') { document.getElementById('learn-grid').classList.add('is-collapsed'); } } catch (e) {}</script>

        {{-- Tombol kecil di tepi menu belajar: menutup / membuka menu --}}
        <button type="button" id="learning-toggle" class="ew-side-toggle" aria-controls="learning-sidebar" aria-expanded="true"
                aria-label="Tutup menu belajar" title="Tutup menu belajar">
            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12.5 4.5l-5 5.5 5 5.5"/></svg>
        </button>

        {{-- ===================== KIRI: PETA BELAJAR (modul & materi, dengan gembok urutan) ===================== --}}
        <aside id="learning-sidebar" class="bg-white border-b-2 md:border-b-0 md:border-r-2 border-line overflow-auto max-h-[46vh] md:max-h-none">
            <div class="px-6 pt-6 pb-4 border-b-2 border-line">
                <div class="eyebrow">{{ $site->site_name }}</div>
                <h2 class="text-xl mt-1 mb-3">Peta Belajar</h2>
                <div class="text-navy text-sm font-semibold">
                    <span><span id="overall-count">{{ $path->completedCount() }}</span> dari {{ $path->totalCount() }} materi selesai</span>
                    <div class="h-2 bg-blue-tint rounded-full mt-2 overflow-hidden">
                        <div id="overall-bar" class="ew-fill h-full bg-blue rounded-full" style="width:{{ $overallPct }}%"></div>
                    </div>
                </div>
            </div>

            <div class="p-2.5">
                @foreach($modules as $m)
                    @php
                        $modulePct = $path->modulePercent($m);
                        $moduleUnlocked = $path->isModuleUnlocked($m);
                        $isOpen = $module->id === $m->id;
                    @endphp
                    <div class="p-1.5">
                        <a href="{{ route('learning.show', $m) }}" data-module-id="{{ $m->id }}"
                           data-locked-msg="Modul ini masih terkunci. Selesaikan modul sebelumnya terlebih dahulu, ya."
                           @unless($moduleUnlocked) aria-disabled="true" tabindex="-1" @endunless
                           class="ew-module flex items-center gap-3 px-3 py-3.5 rounded-xl no-underline text-base {{ $moduleUnlocked ? '' : 'is-locked' }} {{ $isOpen ? 'bg-blue-tint text-blue-dark font-extrabold' : 'text-navy' }}">
                            <span id="module-ring-{{ $m->id }}" class="ew-ring relative w-[38px] h-[38px] rounded-full grid place-items-center shrink-0"
                                  style="background:conic-gradient(var(--color-blue) {{ $modulePct }}%, var(--color-blue-tint) 0)">
                                <span class="absolute w-[28px] h-[28px] bg-white rounded-full"></span>
                                <span class="ew-ring-label relative text-[9px] font-extrabold text-blue-dark">{{ $modulePct }}%</span>
                                <span class="ew-ring-lock"><x-icon-lock /></span>
                            </span>
                            <span class="flex-1 min-w-0">
                                <b class="block">Modul {{ $m->number }}</b>
                                <span class="ew-module-title block text-sm font-normal truncate">{{ $m->title }}</span>
                            </span>
                        </a>

                        @if($isOpen)
                            <div class="pl-3.5 pt-1.5 pb-2">
                                @foreach($m->lessons as $l)
                                    @php
                                        $isCurrent = $lesson?->id === $l->id;
                                        $isDone = $path->isCompleted($l);
                                        $lessonUnlocked = $path->isUnlocked($l);
                                    @endphp
                                    <a href="{{ route('learning.show', [$m, $l]) }}" id="nav-lesson-{{ $l->id }}"
                                       data-locked-msg="Materi ini masih terkunci. Selesaikan materi sebelumnya terlebih dahulu, ya."
                                       @unless($lessonUnlocked) aria-disabled="true" tabindex="-1" title="Terkunci: selesaikan materi sebelumnya dulu" @endunless
                                       @if($isCurrent) aria-current="page" @endif
                                       class="ew-lesson flex items-center gap-3 no-underline px-2.5 py-3 rounded-xl text-base leading-snug {{ $isDone ? 'is-done' : '' }} {{ $lessonUnlocked ? '' : 'is-locked' }}
                                              {{ $isCurrent ? 'bg-blue-tint text-blue-dark font-bold shadow-[inset_3px_0_0_var(--color-blue)]' : 'text-ink' }}">
                                        <span class="ew-badge min-w-[34px] h-[34px] rounded-full grid place-items-center text-[11px] font-extrabold shrink-0 bg-blue-tint/60 text-navy">
                                            <span class="ew-badge-code">{{ $l->code }}</span>
                                            <span class="ew-badge-check">✓</span>
                                            <span class="ew-badge-lock"><x-icon-lock /></span>
                                        </span>
                                        <span>{{ $l->title }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </aside>

        {{-- ===================== KANAN: ISI MATERI ===================== --}}
        <section class="overflow-auto bg-bg px-5 md:px-[5vw] pt-9 pb-16" id="lesson-scroller">
            <div class="flex flex-wrap justify-between gap-2.5 text-navy text-base font-semibold">
                <a href="{{ route('dashboard') }}" class="text-blue-dark no-underline font-bold hover:underline">← Dashboard</a>
                <span>Modul {{ $module->number }} · {{ $module->title }}</span>
            </div>

            @if($lesson)
                <div class="max-w-3xl mx-auto mt-8 mb-6">
                    <div class="eyebrow">{{ $lesson->code }}</div>
                    <h1 class="text-3xl md:text-4xl mt-2 mb-2">{{ $lesson->title }}</h1>
                    <p class="text-ink text-lg">{{ $module->slogan }}</p>
                    <p class="mt-3 mb-0">
                        <span id="lesson-status" class="ew-status" data-state="{{ $statusState }}" role="status">{{ $statusText }}</span>
                    </p>
                </div>

                <div class="max-w-4xl mx-auto card overflow-hidden">
                    @if($lesson->isPdf())
                        <iframe id="pdf-frame" title="Dokumen PDF: {{ $lesson->title }}" class="block w-full h-[70vh] border-0" src="{{ Storage::url($lesson->pdf_path) }}"></iframe>
                    @else
                        <div id="lesson-content" class="lesson-prose p-8 md:p-10 text-lg leading-[1.75] max-w-none">{!! $lesson->renderedContent() !!}</div>
                    @endif

                    {{-- Penanda akhir materi: begitu terlihat di layar, materi dihitung sudah dibaca sampai akhir (100%). --}}
                    <div id="content-end" class="ew-endmark" aria-hidden="true"></div>

                    @if($lesson->hasQuiz())
                        <div id="quiz-box" class="m-6 md:m-8 mt-0 bg-blue-50 border-2 border-line rounded-2xl p-7" style="background:#f2f7fc">
                            <div class="eyebrow">Uji Pemahaman</div>
                            <h2 class="text-2xl mt-1 mb-5">Sudah bisa?</h2>

                            <p id="quiz-lock" class="ew-quiz-lock" @if($readingDone) hidden @endif>
                                <x-icon-lock /> Gulir materi sampai akhir terlebih dahulu, baru latihan ini terbuka.
                            </p>

                            <form method="POST" action="{{ route('learning.quiz', $lesson) }}">
                                @csrf
                                <fieldset id="quiz-fieldset" class="ew-fieldset {{ $readingDone ? '' : 'is-locked' }}" @disabled(! $readingDone)>
                                    @foreach($lesson->quiz as $i => $q)
                                        <div class="my-6">
                                            <h3 class="text-lg text-navy font-bold mb-2">{{ $i + 1 }}. {{ $q['question'] }}</h3>
                                            @foreach($q['options'] as $oi => $opt)
                                                <label class="flex items-center gap-3 bg-white px-4 py-3.5 border-2 border-line rounded-xl my-2.5 font-semibold text-base cursor-pointer hover:border-blue">
                                                    <input type="radio" name="answers[{{ $i }}]" value="{{ $oi }}" required class="w-5.5 h-5.5 shrink-0">
                                                    <span>{{ $opt }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    <button class="btn btn-primary mt-2" data-loading-text="Memeriksa jawaban…">Periksa Jawaban</button>
                                </fieldset>
                            </form>

                            @if(session('quiz_result'))
                                @php $passed = session('quiz_result.passed'); @endphp
                                <div class="mt-5 px-5 py-4 rounded-xl text-lg font-semibold {{ $passed ? 'bg-green-tint text-green' : 'bg-amber-tint text-amber' }}">
                                    Skor Anda: <b>{{ session('quiz_result.score') }}/{{ session('quiz_result.total') }}</b>.
                                    {{ $passed ? 'Materi ini tercatat selesai!' : 'Coba lagi pelan-pelan.' }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Navigasi antar materi + status selesai (otomatis, tanpa tombol "tandai selesai") --}}
                <div class="max-w-4xl mx-auto mt-6">
                    <div class="ew-lessonnav-row">
                        <div>
                            @if($prevLesson)
                                <a class="btn btn-ghost" href="{{ route('learning.show', [$prevLesson->module_id, $prevLesson->id]) }}">← Materi Sebelumnya</a>
                            @endif
                        </div>

                        <div class="text-center">
                            <div id="done-chip" class="inline-block px-5 py-3.5 rounded-xl bg-green-tint text-green font-extrabold text-lg" @unless($completedThis) hidden @endunless>✓ Materi Selesai</div>
                        </div>

                        <div class="text-right">
                            @if($nextLesson)
                                <a id="next-btn" class="btn btn-primary {{ $nextUnlocked ? '' : 'is-disabled' }}"
                                   href="{{ route('learning.show', [$nextLesson->module_id, $nextLesson->id]) }}"
                                   data-locked-msg="Selesaikan materi ini terlebih dahulu agar materi berikutnya terbuka."
                                   @unless($nextUnlocked) aria-disabled="true" tabindex="-1" @endunless>{{ $nextLabel }} →</a>
                            @else
                                <a class="btn btn-primary" href="{{ route('dashboard') }}">Kembali ke Dashboard →</a>
                            @endif
                        </div>
                    </div>
                    @if($nextLesson)
                        <p id="next-hint" class="ew-next-hint" @if($nextUnlocked) hidden @endif>Selesaikan materi ini untuk membuka materi berikutnya.</p>
                    @endif
                </div>
            @else
                <div class="text-center py-24 px-5">
                    <h2 class="text-2xl">Belum ada materi pada modul ini.</h2>
                </div>
            @endif
        </section>
    </div>

    @push('scripts')
        <script type="application/json" id="learning-config">@json($config)</script>
        {{-- SweetAlert2 dipasang dengan versi tetap (pinned) dari cdnjs. Bila gagal dimuat, learning.js memakai alert biasa. --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.all.min.js" defer></script>
        <script src="{{ asset('js/learning.js') }}?v={{ @filemtime(public_path('js/learning.js')) }}" defer></script>
    @endpush
@endsection
