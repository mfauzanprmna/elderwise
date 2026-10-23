@extends('layouts.admin')
@section('content')
    <div class="max-w-4xl mx-auto px-5 md:px-10 py-10 md:py-14">
        <div class="eyebrow">{{ $site->site_name }} · Admin</div>
        <h1 class="text-3xl md:text-4xl mt-2 mb-1">Pengaturan Situs</h1>
        <p class="text-lg text-ink mb-8">Atur nama web, logo, dan ikon tab browser. Perubahan langsung tampil di seluruh halaman.</p>

        @if($errors->any())
            <div class="bg-red-tint text-red rounded-xl px-5 py-4 mb-6">
                <strong>Periksa kembali:</strong>
                <ul class="mt-2 ml-5 list-disc">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settings-form" class="grid grid-cols-1 gap-6">
            @csrf
            @method('PUT')

            {{-- Pratinjau langsung --}}
            <div class="card p-5 md:p-6">
                <h2 class="text-xl mb-1">Pratinjau</h2>
                <p class="text-muted text-base mb-4">Begini tampilan merek di bilah atas dan di tab browser.</p>
                <div class="grid grid-cols-1 gap-4">
                    <div class="border-2 border-line rounded-xl bg-white px-5 h-20 flex items-center overflow-hidden min-w-0">
                        <span id="pv-brand" class="flex items-center gap-3 min-w-0 text-xl"></span>
                    </div>
                    <div class="flex items-center gap-2 bg-[#dfe6f0] rounded-t-xl px-4 py-2.5 max-w-sm text-sm text-ink">
                        <span id="pv-favicon" class="w-4 h-4 shrink-0 grid place-items-center rounded-sm overflow-hidden"></span>
                        <span id="pv-title" class="truncate font-semibold"></span>
                    </div>
                </div>
            </div>

            {{-- Identitas --}}
            <div class="card p-5 md:p-6 grid gap-5">
                <h2 class="text-xl">Identitas</h2>
                <label class="form-label">Nama web
                    <input class="form-input" name="site_name" id="f-name" value="{{ old('site_name', $setting->site_name) }}" required maxlength="60">
                    <small class="text-muted font-normal">Tampil di bilah atas, judul tab browser, halaman masuk/daftar, dan sidebar admin.</small>
                </label>
                <label class="form-label">Tagline (opsional)
                    <input class="form-input" name="tagline" value="{{ old('tagline', $setting->tagline) }}" maxlength="120" placeholder="Contoh: Digital Confidence Journey">
                    <small class="text-muted font-normal">Kalimat singkat di atas judul pada beranda.</small>
                </label>
                <label class="form-label">Deskripsi singkat (opsional)
                    <textarea class="form-input min-h-[54px]" name="description" rows="3" maxlength="300">{{ old('description', $setting->description) }}</textarea>
                    <small class="text-muted font-normal">Tampil di beranda di bawah judul, dan dipakai sebagai deskripsi saat link situs dibagikan atau muncul di mesin pencari.</small>
                </label>
                <label class="form-label">Teks footer (opsional)
                    <input class="form-input" name="footer_text" value="{{ old('footer_text', $setting->footer_text) }}" maxlength="200" placeholder="Contoh: © 2026 Politeknik Negeri Jakarta">
                    <small class="text-muted font-normal">Muncul di bagian bawah halaman peserta. Kosongkan jika tidak ingin menampilkan footer.</small>
                </label>
            </div>

            {{-- Logo & ikon --}}
            <div class="card p-5 md:p-6 grid gap-6">
                <h2 class="text-xl">Logo dan ikon</h2>

                <div class="grid gap-2">
                    <label class="form-label" for="f-logo">Logo</label>
                    <input type="file" name="logo" id="f-logo" accept="image/png,image/jpeg,image/webp" class="form-input">
                    <small class="text-muted">PNG/JPG/WebP, maksimal 2 MB. Latar transparan (PNG) paling bagus. Jika belum ada logo, dipakai lencana huruf pertama nama web.</small>
                    @if($setting->logoUrl())
                        <div class="flex flex-wrap items-center gap-4 mt-1">
                            <img src="{{ $setting->logoUrl() }}" alt="Logo saat ini" class="h-14 w-auto max-w-[220px] object-contain rounded-lg border-2 border-line bg-white p-1.5">
                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-base">
                                <input type="checkbox" name="remove_logo" value="1" id="f-remove-logo" class="w-5 h-5" @checked(old('remove_logo'))>
                                Hapus logo ini
                            </label>
                        </div>
                    @endif
                    <label class="flex items-center gap-2.5 cursor-pointer font-semibold text-lg mt-2">
                        <input type="checkbox" name="show_name_with_logo" value="1" id="f-show-name" class="w-5.5 h-5.5" @checked(old('show_name_with_logo', $errors->any() ? false : $setting->show_name_with_logo))>
                        Tampilkan nama web di samping logo
                    </label>
                    <small class="text-muted -mt-1">Matikan jika logo Anda sudah memuat tulisan nama web.</small>
                </div>

                <div class="grid gap-2 border-t-2 border-line pt-5">
                    <label class="form-label" for="f-favicon">Favicon (ikon tab browser)</label>
                    <input type="file" name="favicon" id="f-favicon" accept="image/png,image/jpeg,image/webp,.ico" class="form-input">
                    <small class="text-muted">PNG atau ICO persegi (misal 64×64 px), maksimal 512 KB. Jika kosong, logo dipakai sebagai favicon.</small>
                    @if($setting->faviconUrl())
                        <div class="flex flex-wrap items-center gap-4 mt-1">
                            <img src="{{ $setting->faviconUrl() }}" alt="Favicon saat ini" class="w-10 h-10 object-contain rounded-lg border-2 border-line bg-white p-1">
                            <label class="flex items-center gap-2 cursor-pointer font-semibold text-base">
                                <input type="checkbox" name="remove_favicon" value="1" id="f-remove-favicon" class="w-5 h-5" @checked(old('remove_favicon'))>
                                Hapus favicon ini
                            </label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-end">
                <button class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>

        {{-- ===================== PENYELENGGARA / MITRA ===================== --}}
        <div class="card p-5 md:p-6 mt-6 grid gap-5" id="penyelenggara">
            <div>
                <h2 class="text-xl mb-1">Penyelenggara dan mitra</h2>
                <p class="text-muted text-base m-0">Logo tampil di bagian bawah halaman peserta dengan judul "Diselenggarakan oleh". Bagian ini disimpan terpisah dari form di atas.</p>
            </div>

            @if($sitePartners->isEmpty())
                <p class="text-muted m-0">Belum ada logo penyelenggara.</p>
            @else
                <div class="grid gap-3">
                    @foreach($sitePartners as $partner)
                        <div class="flex flex-wrap items-center gap-4 border-2 border-line rounded-xl p-3">
                            <div class="bg-white border border-line rounded-lg p-2 w-44 h-20 grid place-items-center shrink-0">
                                <img src="{{ $partner->logoUrl() }}" alt="" class="max-h-full max-w-full object-contain">
                            </div>
                            <div class="flex-1 min-w-[180px]">
                                <strong class="block text-navy">{{ $partner->name }}</strong>
                                @if($partner->url)<small class="text-muted break-all">{{ $partner->url }}</small>@endif
                            </div>
                            <form method="POST" action="{{ route('admin.settings.partners.destroy', $partner) }}" class="m-0"
                                  onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus logo ' . $partner->name . ' dari situs?') }})">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.partners.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 gap-4 border-t-2 border-line pt-5" id="partner-form">
                @csrf
                <h3 class="text-lg">Tambah penyelenggara</h3>
                @if($errors->partner->any())
                    <div class="bg-red-tint text-red rounded-xl px-5 py-4">
                        <ul class="ml-5 list-disc">@foreach($errors->partner->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <label class="form-label">Nama penyelenggara
                    <input class="form-input" name="name" value="{{ old('name') }}" maxlength="120" required placeholder="Contoh: IEEE Indonesia Section">
                </label>
                <label class="form-label">Logo
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-input" required>
                    <small class="text-muted font-normal">PNG/JPG/WebP, maksimal 2 MB. Latar putih atau transparan paling cocok.</small>
                </label>
                <label class="form-label">Tautan website (opsional)
                    <input class="form-input" type="url" name="url" value="{{ old('url') }}" maxlength="255" placeholder="https://...">
                </label>
                <div><button class="btn btn-ghost">+ Tambah Logo</button></div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var nameInput = document.getElementById('f-name');
    var logoInput = document.getElementById('f-logo');
    var faviconInput = document.getElementById('f-favicon');
    var removeLogo = document.getElementById('f-remove-logo');
    var removeFavicon = document.getElementById('f-remove-favicon');
    var showName = document.getElementById('f-show-name');
    var existingLogo = @json($setting->logoUrl());
    var existingFavicon = @json($setting->faviconUrl());
    var logoUrl = null, faviconUrl = null;

    function el(tag, cls, text) { var e = document.createElement(tag); if (cls) e.className = cls; if (text) e.textContent = text; return e; }

    function render() {
        var name = nameInput.value.trim() || 'Nama web';
        var logo = logoUrl || (existingLogo && !(removeLogo && removeLogo.checked) ? existingLogo : null);
        var fav = faviconUrl || (existingFavicon && !(removeFavicon && removeFavicon.checked) ? existingFavicon : null) || logo;

        var brand = document.getElementById('pv-brand');
        brand.innerHTML = '';
        if (logo) {
            var img = el('img', 'w-auto object-contain shrink-0'); img.src = logo; img.alt = ''; img.style.height = '44px'; img.style.maxWidth = '176px';
            brand.appendChild(img);
        } else {
            var badge = el('span', 'grid place-items-center rounded-xl bg-blue text-white font-extrabold shrink-0', name.charAt(0).toUpperCase());
            badge.style.cssText = 'width:44px;height:44px;font-size:20px';
            brand.appendChild(badge);
        }
        if (!logo || showName.checked) brand.appendChild(el('span', 'font-extrabold text-navy leading-tight truncate', name));

        var favBox = document.getElementById('pv-favicon');
        favBox.innerHTML = '';
        if (fav) { var f = el('img'); f.src = fav; f.alt = ''; f.style.cssText = 'width:16px;height:16px;object-fit:contain'; favBox.appendChild(f); }
        else { favBox.textContent = '🌐'; favBox.style.fontSize = '12px'; }
        document.getElementById('pv-title').textContent = name;
    }

    function pick(input, setter) {
        input.addEventListener('change', function () {
            var f = this.files && this.files[0];
            setter(f ? URL.createObjectURL(f) : null);
            render();
        });
    }
    pick(logoInput, function (u) { logoUrl = u; });
    pick(faviconInput, function (u) { faviconUrl = u; });
    [nameInput, showName, removeLogo, removeFavicon].forEach(function (e) { if (e) e.addEventListener('input', render), e && e.addEventListener('change', render); });
    render();
});
</script>
@endpush
@endsection
