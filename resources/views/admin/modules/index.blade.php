@extends('layouts.app')
@section('content')
<div class="admin-shell">
    <div class="dashboard-head">
        <div>
            <div class="eyebrow">ADMIN · MODUL</div>
            <h1>Learning Map</h1>
            <p>Tambah, edit, urutkan, atau hapus modul pembelajaran.</p>
        </div>
        <div class="admin-actions">
            <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">← Admin</a>
            <a class="btn btn-primary" href="{{ route('admin.modules.create') }}">+ Tambah Modul</a>
        </div>
    </div>

    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif

    <div class="table-card">
        <table>
            <thead><tr><th>No.</th><th>Judul</th><th>Slogan</th><th>Badge</th><th>Materi</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($modules as $module)
                <tr>
                    <td><strong>{{ $module->number }}</strong></td>
                    <td><strong>{{ $module->title }}</strong><br><small>{{ $module->description }}</small></td>
                    <td>{{ $module->slogan }}</td>
                    <td>{{ $module->badge ?: '—' }}</td>
                    <td>{{ $module->lessons_count }}</td>
                    <td class="action-cell">
                        <a href="{{ route('admin.modules.edit', $module) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul ini beserta semua materinya?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="link-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-table">Belum ada modul.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
