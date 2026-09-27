@extends('layouts.app')
@section('content')
<div class="admin-shell">
    <div class="dashboard-head">
        <div>
            <div class="eyebrow">ELDERWISE · ADMIN</div>
            <h1>Kelola pembelajaran</h1>
            <p>Perbarui modul, materi, PDF, dan quiz tanpa menyentuh database secara manual.</p>
        </div>
        <div class="admin-actions">
            <a class="btn btn-ghost" href="{{ route('admin.modules.index') }}">Kelola Modul</a>
            <a class="btn btn-ghost" href="{{ route('admin.certificate.edit') }}">Pengaturan Sertifikat</a>
            <a class="btn btn-primary" href="{{ route('admin.lessons.create') }}">+ Tambah Materi</a>
        </div>
    </div>

    <div class="admin-stats">
        <div class="stat-card"><span>MODUL</span><strong>{{ $moduleCount }}</strong><small>modul pembelajaran</small></div>
        <div class="stat-card"><span>MATERI</span><strong>{{ $lessonCount }}</strong><small>submodul / lesson</small></div>
        <div class="stat-card"><span>PESERTA</span><strong>{{ $userCount }}</strong><small>akun terdaftar</small></div>
        <div class="stat-card"><span>ADMIN</span><strong>{{ $adminCount }}</strong><small>akun pengelola</small></div>
    </div>

    <div class="admin-section-head">
        <div><h2>Learning Map</h2><p>Ringkasan modul dan jumlah materi.</p></div>
        <a class="btn btn-ghost" href="{{ route('admin.modules.create') }}">+ Tambah Modul</a>
    </div>

    <div class="admin-module-grid">
        @foreach($modules as $module)
            <article class="admin-module-card">
                <div class="admin-module-top">
                    <span class="module-num">MODUL {{ $module->number }}</span>
                    <span class="tag">{{ $module->lessons_count }} materi</span>
                </div>
                <h3>{{ $module->title }}</h3>
                <p>{{ $module->slogan }}</p>
                <div class="admin-card-actions">
                    <a class="btn btn-ghost btn-sm" href="{{ route('admin.modules.edit', $module) }}">Edit Modul</a>
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.lessons.index', ['module' => $module->id]) }}">Lihat Materi</a>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
