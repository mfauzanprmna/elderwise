@extends('layouts.admin')
@section('content')
    <div class="max-w-7xl mx-auto px-5 md:px-10 py-10 md:py-14">

        <div class="flex flex-wrap justify-between items-end gap-6 mb-8">
            <div>
                <div class="eyebrow">Admin · Pengguna</div>
                <h1 class="text-3xl md:text-4xl mt-2 mb-1">Kelola Pengguna</h1>
                <p class="text-lg text-ink m-0">Tambah akun, ubah data atau peran, reset kata sandi, dan lihat progres belajar tiap pengguna.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="btn btn-ghost" href="{{ route('admin.users.import.template') }}">Unduh Templat Excel</a>
                <button class="btn btn-ghost" type="button" id="open-import-dialog">Impor dari Excel</button>
                <a class="btn btn-primary" href="{{ route('admin.users.create') }}">+ Tambah Pengguna</a>
            </div>
        </div>

        <dialog id="import-user-dialog" aria-labelledby="import-user-title"
                style="width:min(42rem,calc(100% - 2rem));max-height:calc(100% - 2rem);border:0;border-radius:1rem;padding:0;box-shadow:0 14px 34px rgba(11,87,196,.25)">
            <div class="card p-6 md:p-8">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <div class="eyebrow">Kelola Pengguna</div>
                        <h2 id="import-user-title" class="text-2xl mt-1 mb-0">Impor pengguna dari Excel</h2>
                    </div>
                    <button type="button" id="close-import-dialog" class="btn btn-ghost btn-sm" aria-label="Tutup dialog impor">Tutup</button>
                </div>

                <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="grid gap-4">
                    @csrf
                    <label class="form-label">
                        Pilih file Excel atau CSV
                        <input class="form-input mt-1" type="file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted font-normal">Kolom wajib: nama dan email. Maksimal 500 baris dan ukuran file 5 MB. Email yang sudah terdaftar atau berulang akan dilewati.</small>
                    </label>
                    @if($errors->has('file'))
                        <div class="rounded-xl bg-red-tint px-4 py-3 text-red text-sm font-semibold" role="alert">
                            <ul class="m-0 pl-5 list-disc">
                                @foreach($errors->get('file') as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <p class="text-muted text-sm m-0">Semua akun akan dibuat sebagai peserta dengan kata sandi awal <strong>elderwise12</strong>. Pengguna sebaiknya mengganti kata sandi setelah masuk.</p>
                    <div class="flex flex-wrap justify-end gap-3 mt-2">
                        <button class="btn btn-ghost" type="button" id="cancel-import-dialog">Batal</button>
                        <button class="btn btn-primary" type="submit">Impor Pengguna</button>
                    </div>
                </form>
            </div>
        </dialog>


        <div class="grid grid-cols-3 gap-4 mb-6 max-w-xl">
            <div class="card px-5 py-3"><span class="text-muted text-sm font-bold">Semua</span><strong class="block text-2xl text-navy">{{ $countAll }}</strong></div>
            <div class="card px-5 py-3"><span class="text-muted text-sm font-bold">Peserta</span><strong class="block text-2xl text-navy">{{ $countLearners }}</strong></div>
            <div class="card px-5 py-3"><span class="text-muted text-sm font-bold">Admin</span><strong class="block text-2xl text-navy">{{ $countAdmins }}</strong></div>
        </div>

        <div class="card flex flex-wrap items-center justify-between gap-4 p-5 mb-6">
            <div>
                <strong class="block text-navy">Reset progres seluruh peserta</strong>
                <p class="text-muted text-sm m-0 mt-1">Menghapus progres belajar dan pengajuan/sertifikat semua akun peserta. Akun admin tidak terpengaruh.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.reset-all-learning') }}" class="m-0"
                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Reset progres belajar dan pengajuan sertifikat semua peserta? Data sertifikat yang sudah disetujui juga akan dihapus. Tindakan ini tidak bisa dibatalkan.') }})">
                @csrf
                <button class="btn btn-danger">Reset Semua Peserta</button>
            </form>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4 mb-5">
            <label class="form-label flex-1 min-w-[240px]">
                Cari nama atau email
                <input type="search" name="q" value="{{ $q }}" class="form-input" placeholder="Contoh: sutrisno">
            </label>
            <label class="form-label w-48">
                Peran
                <select name="role" class="form-input">
                    <option value="">Semua</option>
                    @foreach(\App\Http\Controllers\AdminUserController::ROLES as $key => $label)
                        <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-label w-52">
                Status sertifikat
                <select name="certificate_status" class="form-input">
                    <option value="">Semua status</option>
                    <option value="not_requested" @selected($certificateStatus === 'not_requested')>Belum diajukan</option>
                    <option value="pending" @selected($certificateStatus === 'pending')>Menunggu persetujuan</option>
                    <option value="approved" @selected($certificateStatus === 'approved')>Disetujui</option>
                </select>
            </label>
            <button class="btn btn-primary">Cari</button>
            @if($q !== '' || $role !== '' || $certificateStatus !== '')
                <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Reset</a>
            @endif
        </form>

        <div class="card overflow-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        @foreach(['Pengguna', 'Peran', 'Progres belajar', 'XP', 'Sertifikat', 'Aksi'] as $h)
                            <th class="text-left px-4 py-4 border-b border-line font-extrabold text-navy whitespace-nowrap" style="background:#f5f9ff">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    @php
                        $pct = $totalLessons > 0 ? round($u->done_count / $totalLessons * 100) : 0;
                        $isSelf = $u->id === auth()->id();
                    @endphp
                    <tr>
                        <td class="px-4 py-4 border-b border-line">
                            <strong class="block">{{ $u->name }}@if($isSelf) <span class="ml-1 text-xs font-extrabold text-blue-dark bg-blue-tint rounded-full px-2 py-0.5">Anda</span>@endif</strong>
                            <small class="block text-muted mt-0.5">{{ $u->email }}</small>
                        </td>
                        <td class="px-4 py-4 border-b border-line">
                            <span class="text-sm font-extrabold rounded-full px-3 py-1 {{ $u->role === 'admin' ? 'bg-amber-tint text-amber' : 'bg-blue-tint text-blue-dark' }}">
                                {{ \App\Http\Controllers\AdminUserController::ROLES[$u->role] ?? ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 border-b border-line min-w-[170px]">
                            <span class="text-base font-semibold">{{ $u->done_count }}/{{ $totalLessons }} materi</span>
                            <div class="h-2 rounded-full bg-blue-tint overflow-hidden mt-1.5 max-w-[150px]">
                                <div class="h-full rounded-full bg-blue" style="width:{{ $pct }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-4 border-b border-line">{{ (int) $u->xp_total }}</td>
                        <td class="px-4 py-4 border-b border-line whitespace-nowrap">
                            @if($u->certificate)
                                @if($u->certificate->isApproved())
                                    <span class="text-green font-bold">✓ Disetujui</span>
                                    <small class="block text-muted">{{ $u->certificate->displayNumber($certSetting) }}</small>
                                @else
                                    <span class="text-amber font-bold">⏳ Menunggu</span>
                                    <small class="block text-muted">Belum disetujui admin</small>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 border-b border-line">
                            <details class="inline-block text-left">
                                <summary class="btn btn-ghost btn-sm cursor-pointer list-none whitespace-nowrap">Aksi <span aria-hidden="true">⌄</span></summary>
                                <div class="flex min-w-max flex-col items-stretch gap-1 pt-2">
                                    <a class="rounded-lg px-3 py-2 text-sm font-semibold text-navy hover:bg-blue-tint" href="{{ route('admin.users.edit', $u) }}">Edit</a>
                                    @if($u->certificate && ! $u->certificate->isApproved())
                                        <form method="POST" action="{{ route('admin.certificate.approve', $u) }}" class="m-0">
                                            @csrf
                                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-blue-dark hover:bg-blue-tint">Setujui Sertifikat</button>
                                        </form>
                                    @endif
                                    @if($u->role === 'learner')
                                        <form method="POST" action="{{ route('admin.users.reset-learning', $u) }}" class="m-0"
                                              onsubmit="return confirm({{ \Illuminate\Support\Js::from('Reset progres belajar dan pengajuan sertifikat ' . $u->name . '? Data sertifikat yang sudah disetujui juga akan dihapus. Tindakan ini tidak bisa dibatalkan.') }})">
                                            @csrf
                                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-amber hover:bg-amber-tint">Reset Progres & Sertifikat</button>
                                        </form>
                                    @endif
                                    @unless($isSelf)
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="m-0"
                                              onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus pengguna ' . $u->name . ' beserta seluruh progres belajar dan sertifikatnya? Tindakan ini tidak bisa dibatalkan.') }})">
                                            @csrf @method('DELETE')
                                            <button class="w-full rounded-lg px-3 py-2 text-left text-sm font-semibold text-red hover:bg-red-50">Hapus Pengguna</button>
                                        </form>
                                    @endunless
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-11">Tidak ada pengguna yang cocok.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4 mt-5">
            <span class="text-muted text-base">
                @if($users->total() > 0)
                    Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                @endif
            </span>
            @if($users->hasPages())
                <div class="flex items-center gap-3">
                    @if($users->onFirstPage())
                        <span class="btn btn-ghost btn-sm opacity-40 pointer-events-none">← Sebelumnya</span>
                    @else
                        <a class="btn btn-ghost btn-sm" href="{{ $users->previousPageUrl() }}">← Sebelumnya</a>
                    @endif
                    <span class="font-semibold text-navy">Hal. {{ $users->currentPage() }}/{{ $users->lastPage() }}</span>
                    @if($users->hasMorePages())
                        <a class="btn btn-ghost btn-sm" href="{{ $users->nextPageUrl() }}">Berikutnya →</a>
                    @else
                        <span class="btn btn-ghost btn-sm opacity-40 pointer-events-none">Berikutnya →</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var dialog = document.getElementById('import-user-dialog');
            var openButton = document.getElementById('open-import-dialog');
            var closeButtons = [
                document.getElementById('close-import-dialog'),
                document.getElementById('cancel-import-dialog')
            ];

            openButton.addEventListener('click', function () { dialog.showModal(); });
            closeButtons.forEach(function (button) {
                button.addEventListener('click', function () { dialog.close(); });
            });
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) dialog.close();
            });
            @if($errors->has('file'))
                dialog.showModal();
            @endif
        })();
    </script>
@endpush
