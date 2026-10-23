@extends('layouts.admin')
@section('content')
    <div class="max-w-3xl mx-auto px-5 md:px-10 py-10 md:py-14">
        <div class="eyebrow">Admin · {{ $module->exists ? 'Edit Modul' : 'Modul Baru' }}</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-1">{{ $module->exists ? 'Edit modul' : 'Buat modul baru' }}</h1>
        <p class="text-muted text-lg mb-7">Nomor modul dibuat otomatis. Lencana modul diatur di bagian bawah.</p>

        @if($errors->any())
            <div class="bg-red-tint text-red rounded-xl px-5 py-4 mb-6">
                <strong>Periksa kembali:</strong>
                <ul class="mt-2 ml-5 list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" id="module-form"
              action="{{ $module->exists ? route('admin.modules.update', $module) : route('admin.modules.store') }}" class="grid gap-5">
            @csrf
            @if($module->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-2 gap-5">
                <label class="form-label">
                    Nomor Modul
                    <input type="number" value="{{ old('number', $module->number) }}" readonly class="form-input bg-bg cursor-not-allowed">
                    <small class="text-muted font-normal">Otomatis: modul terakhir + 1.</small>
                </label>
                <label class="form-label">
                    Urutan Tampil
                    <input type="number" value="{{ old('sort_order', $module->sort_order ?: $module->number) }}" readonly class="form-input bg-bg cursor-not-allowed">
                </label>
            </div>

            <label class="form-label">Judul Modul
                <input class="form-input" name="title" value="{{ old('title', $module->title) }}" required>
            </label>
            <label class="form-label">Slogan / Tagline
                <input class="form-input" name="slogan" value="{{ old('slogan', $module->slogan) }}" required>
            </label>
            <label class="form-label">Deskripsi
                <textarea class="form-input min-h-[54px]" name="description" rows="5">{{ old('description', $module->description) }}</textarea>
            </label>

            {{-- ===================== LENCANA MODUL ===================== --}}
            @php
                $currentColor = old('badge_color', $module->badge_color ?: 'gold');
                $currentIcon = old('badge_icon', $module->badge_icon);
                $emojiChoices = ['🌱','🤝','🔍','✍️','🤖','🏆','⭐','🚀','📚','💡','🛡️','🎯'];
            @endphp
            <div class="border-2 border-line-strong rounded-2xl p-5 md:p-6 bg-white">
                <h2 class="text-xl mb-1">Lencana Modul</h2>
                <p class="text-muted text-base mb-5">Lencana diterima peserta saat seluruh materi modul ini selesai. Pilih emoji atau unggah logo sendiri.</p>

                <div class="grid md:grid-cols-[170px_1fr] gap-6 items-start">
                    {{-- Pratinjau langsung --}}
                    <div class="grid place-items-center gap-2 bg-bg rounded-2xl p-4">
                        <div id="badge-preview" class="rounded-full grid place-items-center border-2 overflow-hidden shadow-elder"
                             style="width:96px;height:96px;font-size:43px"></div>
                        <span id="badge-preview-name" class="text-sm font-bold text-navy text-center leading-tight">Nama lencana</span>
                        <span class="text-xs text-muted">Pratinjau (terbuka)</span>
                    </div>

                    <div class="grid gap-5">
                        <label class="form-label">Nama Lencana
                            <input class="form-input" name="badge" id="badge-name" value="{{ old('badge', $module->badge) }}" placeholder="Contoh: Digital Explorer">
                        </label>

                        <div class="grid gap-2">
                            <label class="form-label" for="badge-icon">Ikon (emoji)</label>
                            <input class="form-input" name="badge_icon" id="badge-icon" value="{{ $currentIcon }}" maxlength="8" placeholder="Ketik atau pilih emoji di bawah">
                            <div class="flex flex-wrap gap-2" role="group" aria-label="Pilihan emoji cepat">
                                @foreach($emojiChoices as $emoji)
                                    <button type="button" data-emoji="{{ $emoji }}"
                                            class="emoji-pick w-12 h-12 rounded-xl border-2 border-line-strong bg-white text-2xl cursor-pointer hover:border-blue">{{ $emoji }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <label class="form-label" for="badge-image">Logo sendiri (opsional)</label>
                            <input type="file" name="badge_image" id="badge-image" accept="image/png,image/jpeg,image/webp" class="form-input">
                            <small class="text-muted">PNG/JPG/WebP, maksimal 2 MB. Jika logo diunggah, logo dipakai menggantikan emoji. Latar transparan (PNG) paling bagus.</small>
                            @if($module->badge_image)
                                <div class="flex items-center gap-3 mt-1">
                                    <img src="{{ $module->badgeImageUrl() }}" alt="Logo lencana saat ini" class="w-12 h-12 object-contain rounded-lg border-2 border-line bg-white p-1">
                                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-base">
                                        <input type="checkbox" name="remove_badge_image" value="1" id="badge-remove" class="w-5 h-5" @checked(old('remove_badge_image'))>
                                        Hapus logo ini (kembali ke emoji)
                                    </label>
                                </div>
                            @endif
                        </div>

                        <fieldset class="grid gap-2">
                            <legend class="font-bold text-lg text-navy mb-1">Warna medali</legend>
                            <div class="flex flex-wrap gap-4">
                                @foreach(\App\Models\Module::BADGE_COLORS as $key => $col)
                                    <label class="flex flex-col items-center gap-1.5 cursor-pointer">
                                        <input type="radio" name="badge_color" value="{{ $key }}" class="peer sr-only badge-color-radio" @checked($currentColor === $key)>
                                        <span class="w-12 h-12 rounded-full border-2 peer-checked:ring-4 peer-checked:ring-blue/40 peer-focus-visible:ring-4 peer-focus-visible:ring-blue"
                                              style="background:linear-gradient(135deg,{{ $col['from'] }},{{ $col['to'] }});border-color:{{ $col['border'] }}"></span>
                                        <span class="text-sm font-semibold text-ink">{{ $col['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-2">
                <a class="btn btn-ghost" href="{{ route('admin.modules.index') }}">Batal</a>
                <button class="btn btn-primary">{{ $module->exists ? 'Simpan Perubahan' : 'Buat Modul & Kelola Materi →' }}</button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const preview = document.getElementById('badge-preview');
    const previewName = document.getElementById('badge-preview-name');
    const nameInput = document.getElementById('badge-name');
    const iconInput = document.getElementById('badge-icon');
    const fileInput = document.getElementById('badge-image');
    const removeBox = document.getElementById('badge-remove');
    const colors = @json(collect(\App\Models\Module::BADGE_COLORS)->map(fn ($c) => ['from' => $c['from'], 'to' => $c['to'], 'border' => $c['border']]));
    const existingImage = @json($module->badgeImageUrl());
    const fallbackIcon = @json($module->exists ? $module->badgeIcon() : '🎯');
    let uploadedUrl = null;

    function render() {
        const key = (document.querySelector('.badge-color-radio:checked') || {}).value || 'gold';
        const c = colors[key];
        preview.style.background = `linear-gradient(135deg, ${c.from}, ${c.to})`;
        preview.style.borderColor = c.border;

        let imgSrc = uploadedUrl;
        if (!imgSrc && existingImage && !(removeBox && removeBox.checked)) imgSrc = existingImage;

        preview.innerHTML = '';
        if (imgSrc) {
            const img = document.createElement('img');
            img.src = imgSrc;
            img.alt = '';
            img.style.cssText = 'width:68%;height:68%;object-fit:contain';
            preview.appendChild(img);
        } else {
            const span = document.createElement('span');
            span.textContent = iconInput.value.trim() || fallbackIcon;
            preview.appendChild(span);
        }
        previewName.textContent = nameInput.value.trim() || 'Nama lencana';
    }

    fileInput.addEventListener('change', function () {
        if (uploadedUrl) URL.revokeObjectURL(uploadedUrl);
        uploadedUrl = this.files && this.files[0] ? URL.createObjectURL(this.files[0]) : null;
        render();
    });
    document.querySelectorAll('.emoji-pick').forEach(btn => btn.addEventListener('click', function () {
        iconInput.value = this.dataset.emoji;
        // memilih emoji = tidak memakai logo unggahan yang baru dipilih
        fileInput.value = '';
        if (uploadedUrl) { URL.revokeObjectURL(uploadedUrl); uploadedUrl = null; }
        if (removeBox) removeBox.checked = true;
        render();
    }));
    [nameInput, iconInput].forEach(el => el.addEventListener('input', render));
    document.querySelectorAll('.badge-color-radio').forEach(r => r.addEventListener('change', render));
    if (removeBox) removeBox.addEventListener('change', render);
    render();
});
</script>
@endpush
@endsection
