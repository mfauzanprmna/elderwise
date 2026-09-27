@extends('layouts.app')
@section('content')
<div class="admin-shell">
    <div class="admin-head">
        <div>
            <div class="eyebrow">ADMIN · MATERI</div>
            <h1>Materi & Submodul</h1>
            <p class="muted">Quiz bersifat opsional. Isi materi dapat berupa editor teks atau PDF.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.lessons.create', $selectedModule ? ['module' => $selectedModule] : []) }}">+ Tambah Materi</a>
    </div>

    @if(session('success'))<div class="alert success-box">{{ session('success') }}</div>@endif

    <form method="GET" class="filter-bar">
        <label>Modul
            <select name="module" onchange="this.form.submit()">
                <option value="">Semua Modul</option>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}" @selected((string)$selectedModule === (string)$module->id)>Modul {{ $module->number }} — {{ $module->title }}</option>
                @endforeach
            </select>
        </label>
    </form>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Kode</th><th>Materi</th><th>Jenis</th><th>Quiz</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($lessons as $lesson)
                <tr>
                    <td><strong>{{ $lesson->code }}</strong></td>
                    <td><strong>{{ $lesson->title }}</strong><small>Modul {{ $lesson->module->number }} · {{ $lesson->module->title }}</small></td>
                    <td>{{ $lesson->type === 'pdf' ? 'PDF' : 'Teks' }}</td>
                    <td>{{ $lesson->quiz ? 'Ada' : 'Tidak' }}</td>
                    <td class="actions">
                        <a class="btn btn-small btn-ghost" href="{{ route('admin.lessons.edit', $lesson) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini?')">
                            @csrf @method('DELETE')<button class="btn btn-small btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">Belum ada materi pada modul ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
