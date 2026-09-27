@extends('layouts.app')
@section('content')
<div class="admin-shell narrow">
    <div class="eyebrow">ADMIN · {{ $module->exists ? 'EDIT MODUL' : 'MODUL BARU' }}</div>
    <h1>{{ $module->exists ? 'Edit modul' : 'Buat modul baru' }}</h1>
    <p class="muted">Nomor modul dibuat otomatis berdasarkan modul terakhir.</p>

    @if($errors->any())
        <div class="alert error-box"><strong>Periksa kembali:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ $module->exists ? route('admin.modules.update', $module) : route('admin.modules.store') }}" class="form-stack">
        @csrf
        @if($module->exists) @method('PUT') @endif

        <div class="form-grid-2">
            <label>Nomor Modul
                <input type="number" name="number_display" value="{{ old('number', $module->number) }}" readonly class="input-readonly">
                <small>Otomatis: modul terakhir + 1.</small>
            </label>
            <label>Urutan Tampil
                <input type="number" name="sort_order_display" value="{{ old('sort_order', $module->sort_order ?: $module->number) }}" readonly class="input-readonly">
            </label>
        </div>

        <label>Judul Modul<input name="title" value="{{ old('title', $module->title) }}" required></label>
        <label>Slogan / Tagline<input name="slogan" value="{{ old('slogan', $module->slogan) }}" required></label>
        <label>Deskripsi<textarea name="description" rows="5">{{ old('description', $module->description) }}</textarea></label>
        <label>Badge<input name="badge" value="{{ old('badge', $module->badge) }}" placeholder="Digital Explorer"></label>

        <div class="form-actions">
            <a class="btn btn-ghost" href="{{ route('admin.modules.index') }}">Batal</a>
            <button class="btn btn-primary">{{ $module->exists ? 'Simpan Perubahan' : 'Buat Modul & Kelola Materi →' }}</button>
        </div>
    </form>
</div>
@endsection
