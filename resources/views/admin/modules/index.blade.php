@extends('layouts.admin')
@section('content')
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-10 md:py-14">

        <div class="flex flex-wrap justify-between items-end gap-6 mb-8">
            <div>
                <div class="eyebrow">Admin · Modul</div>
                <h1 class="text-3xl md:text-4xl mt-2 mb-1">Learning Map</h1>
                <p class="text-lg text-ink m-0">Tambah, edit, urutkan, atau hapus modul pembelajaran.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="btn btn-primary" href="{{ route('admin.modules.create') }}">+ Tambah Modul</a>
            </div>
        </div>


        <div class="card overflow-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">No.</th>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">Judul</th>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">Slogan</th>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">Badge</th>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">Materi</th>
                        <th class="text-left px-4 py-4 bg-blue-50 font-extrabold text-navy border-b border-line" style="background:#f5f9ff">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($modules as $module)
                    <tr>
                        <td class="px-4 py-4 border-b border-line"><strong>{{ $module->number }}</strong></td>
                        <td class="px-4 py-4 border-b border-line">
                            <strong class="block">{{ $module->title }}</strong>
                            <small class="block text-muted mt-1">{{ $module->description }}</small>
                        </td>
                        <td class="px-4 py-4 border-b border-line">{{ $module->slogan }}</td>
                        <td class="px-4 py-4 border-b border-line">
                            <div class="flex items-center gap-3">
                                <x-badge-medal :module="$module" state="done" :size="40" />
                                <span>{{ $module->badge ?: '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 border-b border-line">{{ $module->lessons_count }}</td>
                        <td class="px-4 py-4 border-b border-line">
                            <div class="flex items-center gap-3.5">
                                <a class="text-blue-dark font-bold" href="{{ route('admin.modules.edit', $module) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.modules.destroy', $module) }}" onsubmit="return confirm('Hapus modul ini beserta semua materinya?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="border-0 bg-transparent text-red font-bold cursor-pointer">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-11">Belum ada modul.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
