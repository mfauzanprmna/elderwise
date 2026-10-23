@extends('layouts.admin')
@section('content')
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-10 md:py-14">

        <div class="flex flex-wrap justify-between items-end gap-6 mb-8">
            <div>
                <div class="eyebrow">{{ $site->site_name }} · Admin</div>
                <h1 class="text-3xl md:text-4xl mt-2 mb-1">Kelola pembelajaran</h1>
                <p class="text-lg text-ink m-0">Perbarui modul, materi, PDF, dan quiz tanpa menyentuh database secara manual.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ route('admin.lessons.create') }}">+ Tambah Materi</a>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-11">
            @foreach([
                ['label' => 'Modul', 'value' => $moduleCount, 'note' => 'modul pembelajaran'],
                ['label' => 'Materi', 'value' => $lessonCount, 'note' => 'submodul / lesson'],
                ['label' => 'Peserta', 'value' => $userCount, 'note' => 'akun terdaftar'],
                ['label' => 'Admin', 'value' => $adminCount, 'note' => 'akun pengelola'],
            ] as $stat)
                <div class="card p-6">
                    <span class="text-muted text-sm font-extrabold tracking-wide">{{ $stat['label'] }}</span>
                    <strong class="block text-navy text-4xl leading-tight my-2">{{ $stat['value'] }}</strong>
                    <small class="text-muted text-sm">{{ $stat['note'] }}</small>
                </div>
            @endforeach
        </div>

        <div class="flex flex-wrap justify-between items-end gap-5 mb-5">
            <div>
                <h2 class="text-2xl mb-1">Learning Map</h2>
                <p class="text-ink m-0">Ringkasan modul dan jumlah materi.</p>
            </div>
            <a class="btn btn-ghost" href="{{ route('admin.modules.create') }}">+ Tambah Modul</a>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($modules as $module)
                <article class="card p-6">
                    <div class="flex justify-between items-center">
                        <span class="text-muted text-sm font-extrabold tracking-wide">Modul {{ $module->number }}</span>
                        <span class="bg-blue-tint text-blue-dark text-xs font-extrabold rounded-full px-2.5 py-1">{{ $module->lessons_count }} materi</span>
                    </div>
                    <h3 class="text-xl leading-snug mt-5 mb-1.5">{{ $module->title }}</h3>
                    <p class="text-ink min-h-[3rem]">{{ $module->slogan }}</p>
                    <div class="flex flex-wrap gap-2.5 mt-4">
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.modules.edit', $module) }}">Edit Modul</a>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.lessons.index', ['module' => $module->id]) }}">Lihat Materi</a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection
