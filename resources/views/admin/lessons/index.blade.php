@extends('layouts.admin')
@section('content')
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-10 md:py-14">

        <div class="flex flex-wrap justify-between items-end gap-6 mb-6">
            <div>
                <div class="eyebrow">Admin · Materi</div>
                <h1 class="text-3xl md:text-4xl mt-2 mb-1">Materi & Submodul</h1>
                <p class="text-muted text-lg m-0">Quiz bersifat opsional. Isi materi dapat berupa editor teks atau PDF.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('admin.lessons.create', $selectedModule ? ['module' => $selectedModule] : []) }}">+ Tambah Materi</a>
        </div>


        <form method="GET" class="mb-5">
            <label class="form-label max-w-sm">
                Modul
                <select name="module" onchange="this.form.submit()" class="form-input">
                    <option value="">Semua Modul</option>
                    @foreach($modules as $module)
                        <option value="{{ $module->id }}" @selected((string) $selectedModule === (string) $module->id)>
                            Modul {{ $module->number }} — {{ $module->title }}
                        </option>
                    @endforeach
                </select>
            </label>
        </form>

        <div class="card overflow-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy" style="background:#f5f9ff">Kode</th>
                        <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy" style="background:#f5f9ff">Materi</th>
                        <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy" style="background:#f5f9ff">Jenis</th>
                        <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy" style="background:#f5f9ff">Quiz</th>
                        <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy" style="background:#f5f9ff">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lessons as $lesson)
                    <tr>
                        <td class="px-4 py-4 border-b border-line"><strong>{{ $lesson->code }}</strong></td>
                        <td class="px-4 py-4 border-b border-line">
                            <strong class="block">{{ $lesson->title }}</strong>
                            <small class="block text-muted mt-1">Modul {{ $lesson->module->number }} · {{ $lesson->module->title }}</small>
                        </td>
                        <td class="px-4 py-4 border-b border-line">{{ $lesson->type === 'pdf' ? 'PDF' : 'Teks' }}</td>
                        <td class="px-4 py-4 border-b border-line">{{ $lesson->quiz ? 'Ada' : 'Tidak' }}</td>
                        <td class="px-4 py-4 border-b border-line">
                            <div class="flex items-center gap-2.5">
                                <a class="btn btn-ghost btn-sm" href="{{ route('admin.lessons.edit', $lesson) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini?')" class="m-0">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-11">Belum ada materi pada modul ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
