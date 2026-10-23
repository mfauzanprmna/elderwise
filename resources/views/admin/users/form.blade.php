@extends('layouts.admin')
@section('content')
    @php $isSelf = $user->exists && $user->id === auth()->id(); @endphp
    <div class="max-w-2xl mx-auto px-5 md:px-10 py-10 md:py-14">
        <div class="eyebrow">Admin · {{ $user->exists ? 'Edit Pengguna' : 'Pengguna Baru' }}</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-1">{{ $user->exists ? 'Edit pengguna' : 'Tambah pengguna' }}</h1>
        <p class="text-muted text-lg mb-7">
            {{ $user->exists ? 'Ubah data akun. Kolom kata sandi boleh dikosongkan jika tidak ingin mengubahnya.' : 'Buat akun baru untuk peserta atau admin.' }}
        </p>

        @if($user->exists)
            <div class="card p-5 mb-6 grid sm:grid-cols-3 gap-4">
                <div><span class="text-muted text-sm font-bold">Progres belajar</span><strong class="block text-xl text-navy">{{ $user->done_count }}/{{ $totalLessons }} materi</strong></div>
                <div><span class="text-muted text-sm font-bold">Total XP</span><strong class="block text-xl text-navy">{{ (int) $user->xp_total }}</strong></div>
                <div><span class="text-muted text-sm font-bold">Sertifikat</span><strong class="block text-xl {{ $user->certificate ? 'text-green' : 'text-muted' }}">{{ $user->certificate ? '✓ ' . $user->certificate->displayNumber($certSetting) : 'Belum terbit' }}</strong></div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-tint text-red rounded-xl px-5 py-4 mb-6">
                <strong>Periksa kembali:</strong>
                <ul class="mt-2 ml-5 list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="user-form" autocomplete="off"
              action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" class="grid gap-5">
            @csrf
            @if($user->exists) @method('PUT') @endif

            <label class="form-label">Nama lengkap
                <input class="form-input" name="name" value="{{ old('name', $user->name) }}" required maxlength="120">
            </label>

            <label class="form-label">Email
                <input class="form-input" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="190">
            </label>

            <div class="grid gap-2">
                <label class="form-label" for="role">Peran</label>
                @if($isSelf)
                    <input type="hidden" name="role" value="admin">
                    <select id="role" class="form-input bg-bg cursor-not-allowed" disabled>
                        <option>Admin</option>
                    </select>
                    <small class="text-muted">Ini akun Anda sendiri, jadi perannya tidak bisa diturunkan.</small>
                @else
                    <select id="role" name="role" class="form-input" required>
                        @foreach($roles as $key => $label)
                            <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Peserta hanya mengakses materi belajar. Admin juga bisa mengelola modul, materi, sertifikat, dan pengguna.</small>
                @endif
            </div>

            <label class="form-label">{{ $user->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}
                <input class="form-input pw-field" type="password" name="password" minlength="8" autocomplete="new-password" @required(! $user->exists)>
                <small class="text-muted font-normal">Minimal 8 karakter.</small>
            </label>
            <label class="form-label">Ulangi kata sandi
                <input class="form-input pw-field" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" @required(! $user->exists)>
            </label>
            <label class="flex items-center gap-2.5 font-semibold text-lg cursor-pointer">
                <input type="checkbox" id="show-password" class="w-5.5 h-5.5"> Tampilkan kata sandi
            </label>

            <div class="flex justify-end gap-3 mt-2">
                <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Batal</a>
                <button class="btn btn-primary">{{ $user->exists ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
document.getElementById('show-password').addEventListener('change', function () {
    document.querySelectorAll('.pw-field').forEach(function (el) { el.type = this.checked ? 'text' : 'password'; }, this);
});
</script>
@endpush
@endsection
