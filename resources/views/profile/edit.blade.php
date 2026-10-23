@extends('layouts.app')
@section('content')
    @php $avatar = $user->avatarUrl(); @endphp
    <div class="max-w-3xl mx-auto px-5 md:px-8 py-10 md:py-14">
        <div class="eyebrow">Akun Saya</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-2">Pengaturan Akun</h1>
        <p class="text-muted text-lg m-0">Atur nama dan foto profil Anda.</p>

        <nav class="ew-tabs" aria-label="Pengaturan akun">
            <a href="{{ route('profile.edit') }}" aria-current="page">Profil</a>
            <a href="{{ route('profile.password') }}">Ganti Kata Sandi</a>
        </nav>

        <div class="card p-8 md:p-10 mt-6">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="grid gap-7">
                @csrf

                <div class="ew-photo-row">
                    <span id="avatar-preview" class="ew-avatar ew-avatar-lg" aria-hidden="true">
                        {{ $user->initials() }}
                        @if($avatar)<img src="{{ $avatar }}" alt="" referrerpolicy="no-referrer" onerror="this.remove()">@endif
                    </span>
                    <div>
                        <label class="form-label" for="avatar">Foto profil</label>
                        <input id="avatar" class="form-input" type="file" name="avatar"
                               accept="image/png,image/jpeg,image/webp" data-max-kb="2048" data-avatar-preview="#avatar-preview">
                        <p class="ew-hint">Format JPG, PNG, atau WebP, maksimal 2 MB. Foto ini tampil di pojok kanan atas.</p>
                        @if($user->avatar_url && ! $user->avatar_path)
                            <p class="ew-hint">Saat ini memakai foto dari akun Google Anda. Unggah foto di sini untuk menggantinya.</p>
                        @endif
                        @error('avatar')<div class="text-red text-sm font-semibold mt-1" role="alert">{{ $message }}</div>@enderror

                        @if($user->avatar_path)
                            <label class="flex items-center gap-2.5 font-semibold text-base mt-3">
                                <input type="checkbox" name="remove_avatar" value="1" class="w-5.5 h-5.5">
                                Hapus foto saya
                            </label>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="form-label" for="name">Nama lengkap</label>
                    <input id="name" class="form-input" name="name" value="{{ old('name', $user->name) }}" required maxlength="120" autocomplete="name">
                    @error('name')<div class="text-red text-sm font-semibold mt-1" role="alert">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label class="form-label" for="email">Email</label>
                    <input id="email" class="form-input" type="email" value="{{ $user->email }}" readonly aria-describedby="email-hint" style="background:#f4f7fb">
                    <p id="email-hint" class="ew-hint">Email dipakai untuk masuk dan tidak dapat diubah sendiri. Hubungi penyelenggara bila perlu mengubahnya.</p>
                </div>

                <button class="btn btn-primary" data-loading-text="Menyimpan…">Simpan Perubahan</button>
            </form>
        </div>
    </div>
@endsection
