@extends('layouts.admin')
@section('content')
    <div class="max-w-5xl mx-auto px-5 md:px-10 py-10 md:py-14">

        <div class="flex flex-wrap justify-between items-end gap-6 mb-8">
            <div>
                <div class="eyebrow">{{ $site->site_name }} · Admin</div>
                <h1 class="text-3xl md:text-4xl mt-2 mb-1">Pengaturan Sertifikat</h1>
                <p class="text-lg text-ink m-0">Unggah gambar latar sertifikat, lalu geser tiap tulisan ke posisi yang diinginkan.</p>
            </div>
        </div>

        {{-- ===================== UPLOAD LATAR ===================== --}}
        <div class="mb-4">
            <h2 class="text-2xl mb-1">1. Gambar Latar Sertifikat</h2>
            <p class="text-ink m-0">Format JPG/PNG, ukuran landscape disarankan (misal 1600×1130 px).</p>
        </div>
        <div class="card p-6 mb-11">
            <form method="POST" action="{{ route('admin.certificate.background') }}" enctype="multipart/form-data" class="grid gap-4 max-w-lg">
                @csrf
                <label class="form-label">
                    Pilih gambar latar baru
                    <input type="file" name="background" accept="image/png,image/jpeg" required class="form-input">
                </label>
                @error('background')<div class="text-red text-sm font-semibold">{{ $message }}</div>@enderror
                <button class="btn btn-primary">Unggah Gambar</button>
            </form>
            @if($setting->background_path)
                <p class="mt-4 inline-block px-3.5 py-2.5 rounded-lg bg-green-tint text-green font-semibold">
                    ✓ Gambar latar aktif: {{ basename($setting->background_path) }} ({{ $setting->background_width }}×{{ $setting->background_height }}px)
                </p>
            @else
                <p class="text-muted mt-4">Belum ada gambar latar. Unggah dulu sebelum mengatur posisi tulisan.</p>
            @endif
        </div>

        {{-- ===================== FORMAT NOMOR SERTIFIKAT ===================== --}}
        <div class="mb-4">
            <h2 class="text-2xl mb-1">2. Format Nomor Sertifikat</h2>
            <p class="text-ink m-0">Tulis format nomornya, lalu sisipkan kode otomatis. Kode itu diisi sistem setiap kali peserta mengunduh sertifikatnya.</p>
        </div>
        @php
            $numberTemplate = old('number_template', $setting->number_template ?: \App\Models\CertificateSetting::DEFAULT_NUMBER_TEMPLATE);
            $resetChecked = old('number_template') !== null ? (bool) old('number_reset_yearly') : (bool) $setting->number_reset_yearly;
        @endphp
        <div class="card p-6 mb-11">
            <form method="POST" action="{{ route('admin.certificate.number') }}" id="number-form" class="grid gap-5 max-w-2xl"
                  data-year="{{ $sample['sample_year'] }}" data-seq="{{ $sample['sample_sequence'] }}">
                @csrf
                <div>
                    <label class="form-label" for="number-template">Format nomor</label>
                    <input id="number-template" class="form-input" name="number_template" value="{{ $numberTemplate }}"
                           maxlength="120" required autocomplete="off" placeholder="Elderwise/{TAHUN}/{URUT}">
                    @error('number_template')<div class="text-red text-sm font-semibold mt-1" role="alert">{{ $message }}</div>@enderror
                    <div class="flex flex-wrap items-center gap-2.5 mt-3">
                        <span class="text-muted text-sm font-bold">Sisipkan kode:</span>
                        <button type="button" class="btn btn-ghost btn-sm" data-insert="{TAHUN}">+ {TAHUN}</button>
                        <button type="button" class="btn btn-ghost btn-sm" data-insert="{URUT}">+ {URUT}</button>
                    </div>
                </div>

                <div class="rounded-xl bg-blue-tint px-5 py-4 text-lg">
                    Contoh hasil untuk peserta berikutnya:
                    <b class="text-navy" id="number-example">—</b>
                </div>

                <ul class="m-0 pl-5 text-ink text-base leading-relaxed grid gap-1.5">
                    <li><b>{TAHUN}</b> diisi tahun peserta lulus (tahun sertifikatnya terbit), misalnya <b>{{ $sample['sample_year'] }}</b>.</li>
                    <li><b>{URUT}</b> diisi nomor urut persetujuan sertifikat 3 angka: peserta pertama <b>001</b>, kedua <b>002</b>, lalu <b>010</b>, <b>100</b>, dengan batas maksimal <b>999</b>.</li>
                    <li>Kode yang sama juga bisa dipakai di isi tulisan tambahan pada bagian 4 di bawah.</li>
                </ul>

                <label class="flex items-center gap-2.5 font-semibold text-lg">
                    <input type="checkbox" name="number_reset_yearly" value="1" class="w-5.5 h-5.5" @checked($resetChecked)>
                    Mulai ulang nomor urut dari 001 setiap tahun
                </label>

                <button class="btn btn-primary" data-loading-text="Menyimpan…">Simpan Format Nomor</button>
            </form>
        </div>

        @if($setting->background_path)
            {{-- ===================== BUILDER POSISI ===================== --}}
            <div class="flex flex-wrap justify-between items-end gap-5 mb-4">
                <div>
                    <h2 class="text-2xl mb-1">3. Atur Posisi Tulisan</h2>
                    <p class="text-ink m-0">Geser setiap kotak ke tempat yang sesuai di atas gambar. Teks yang tampil adalah <b>contoh isi</b>, supaya Bapak/Ibu bisa melihat perkiraan hasil aslinya.</p>
                </div>
                <a href="{{ route('admin.certificate.preview') }}" target="_blank" class="btn btn-ghost">🔍 Lihat Pratinjau PDF</a>
            </div>

            <form method="POST" action="{{ route('admin.certificate.fields') }}" id="certificate-form">
                @csrf
                <input type="hidden" name="title" value="{{ $setting->title }}">
                <div id="fields-data-holder"></div>

                <div class="cert-builder">
                    <div class="cert-canvas-wrap">
                        <div class="cert-canvas" id="cert-canvas" data-sample-year="{{ $sample['sample_year'] }}" data-sample-seq="{{ $sample['sample_sequence'] }}" data-bg-width="{{ $setting->background_width ?? 1600 }}" data-bg-src="{{ $setting->background_path ? Storage::disk('public')->url($setting->background_path) : '' }}" style="background-image:url('{{ $setting->background_path ? Storage::disk('public')->url($setting->background_path) : '' }}')">
                            <div class="cert-canvas-error" id="cert-canvas-error" hidden>
                                ⚠️ Gambar latar gagal dimuat. Pastikan file ada di folder <code>public/uploads</code> dan <code>UPLOADS_URL</code> di .env sudah benar, lalu muat ulang halaman ini.
                            </div>
                            @foreach($fields as $i => $f)
                                @php
                                    $sampleText = match($f['source'] ?? null) {
                                        'user_name' => $sample['user_name'],
                                        'issued_date' => $sample['issued_date'],
                                        'certificate_number' => $sample['certificate_number'],
                                        default => \App\Models\CertificateSetting::fillTemplate((string) ($f['value'] ?: $f['label']), $sample['sample_year'], $sample['sample_sequence']),
                                    };
                                @endphp
                                <div class="cert-token align-{{ $f['align'] }} {{ ($f['visible'] ?? true) ? '' : 'is-hidden' }}"
                                     data-index="{{ $i }}"
                                     data-key="{{ $f['key'] }}"
                                     data-source="{{ $f['source'] ?? '' }}"
                                     data-fontsize="{{ $f['font_size'] }}"
                                     style="left:{{ $f['x'] }}%; top:{{ $f['y'] }}%; color:{{ $f['color'] }}; font-weight:{{ !empty($f['bold']) ? '700':'400' }}; text-align:{{ $f['align'] }};">
                                    <span class="cert-token-label">{{ $f['label'] }}</span>
                                    <span class="cert-token-sample">{{ $sampleText }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-muted text-sm mt-3">Seret kotak teks ke posisi yang diinginkan. Ukuran huruf di sini mengikuti skala asli, dan teks yang tampil adalah contoh isi supaya mudah dicek apakah pas atau kepotong. Klik "Lihat Pratinjau PDF" untuk melihat hasil sebenarnya.</p>
                        <p class="text-muted text-sm mt-2"><b>Halaman 2</b> sertifikat (rincian materi seperti rapor dan total JP) dibuat otomatis dari daftar modul, 1 modul = 1 JP. Tidak perlu diatur di sini, dan ikut muncul di pratinjau PDF.</p>
                    </div>

                    <div class="cert-panel">
                        <h3 class="text-xl mt-0 mb-4">Daftar Tulisan</h3>
                        <div id="cert-field-list">
                            @foreach($fields as $i => $f)
                                <div class="cert-field-row" data-index="{{ $i }}">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" class="f-visible w-5 h-5" {{ ($f['visible'] ?? true) ? 'checked':'' }}>
                                            <b class="text-navy">{{ $f['label'] }}</b>
                                        </label>
                                        @if(($f['kind'] ?? 'dynamic') === 'custom')
                                            <form method="POST" action="{{ route('admin.certificate.fields.remove', $f['key']) }}" class="f-remove-form m-0" onsubmit="return confirm('Hapus tulisan ini dari sertifikat?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="border-0 bg-transparent text-red font-bold text-sm cursor-pointer">Hapus</button>
                                            </form>
                                        @else
                                            <span class="bg-blue-tint text-blue-dark text-xs font-extrabold rounded-full px-2.5 py-1">bawaan</span>
                                        @endif
                                    </div>

                                    @if(($f['kind'] ?? 'dynamic') === 'custom' || $f['source'] === 'program_name')
                                        <label class="cert-mini-label">Teks / isi
                                            <input type="text" class="f-value" value="{{ $f['value'] }}" maxlength="500">
                                        </label>
                                        <p class="text-sm text-muted mt-1.5 leading-relaxed">Bisa memakai kode otomatis <b>{TAHUN}</b> dan <b>{URUT}</b>.</p>
                                    @else
                                        <p class="text-sm text-muted mt-1.5 leading-relaxed">
                                            Isi otomatis: {{ match($f['source']) { 'user_name' => 'nama peserta yang login', 'issued_date' => 'tanggal sertifikat dibuat', 'certificate_number' => 'nomor sesuai Format Nomor Sertifikat (bagian 2)', default => '-' } }}
                                            <br>Contoh: <i>{{ match($f['source']) { 'user_name' => $sample['user_name'], 'issued_date' => $sample['issued_date'], 'certificate_number' => $sample['certificate_number'], default => '-' } }}</i>
                                        </p>
                                    @endif

                                    <div class="grid grid-cols-2 gap-3">
                                        <label class="cert-mini-label">Ukuran huruf
                                            <input type="number" class="f-fontsize" value="{{ $f['font_size'] }}" min="8" max="120">
                                        </label>
                                        <label class="cert-mini-label">Warna
                                            <input type="color" class="f-color" value="{{ $f['color'] }}">
                                        </label>
                                    </div>
                                    <label class="cert-mini-label">Perataan teks
                                        <select class="f-align">
                                            <option value="left" {{ $f['align'] === 'left' ? 'selected' : '' }}>Kiri</option>
                                            <option value="center" {{ $f['align'] === 'center' ? 'selected' : '' }}>Tengah</option>
                                            <option value="right" {{ $f['align'] === 'right' ? 'selected' : '' }}>Kanan</option>
                                        </select>
                                    </label>
                                    <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                        <input type="checkbox" class="f-bold w-5 h-5" {{ !empty($f['bold']) ? 'checked' : '' }}>
                                        <span class="text-sm font-semibold">Huruf tebal</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary w-full mt-4">Simpan Pengaturan Sertifikat</button>
                    </div>
                </div>
            </form>

            {{-- ===================== TAMBAH FIELD CUSTOM ===================== --}}
            <div class="mt-11 mb-4">
                <h2 class="text-2xl mb-1">4. Tambah Tulisan Baru</h2>
                <p class="text-ink m-0">Misalnya "Diselenggarakan oleh", "Durasi Pelatihan", atau nama penandatangan.</p>
            </div>
            <div class="card p-6">
                <form method="POST" action="{{ route('admin.certificate.fields.add') }}" class="grid sm:grid-cols-2 gap-4 items-end">
                    @csrf
                    <label class="form-label">
                        Nama tulisan (label)
                        <input type="text" name="label" placeholder="Contoh: Diselenggarakan oleh" required maxlength="120" class="form-input">
                    </label>
                    <label class="form-label">
                        Isi teks
                        <input type="text" name="value" placeholder="Contoh: Politeknik Negeri Jakarta" maxlength="500" class="form-input">
                        <span class="text-sm text-muted font-normal">Bisa memakai kode otomatis {TAHUN} dan {URUT}, misalnya "Angkatan {TAHUN}".</span>
                    </label>
                    <button class="btn btn-ghost sm:col-span-2">+ Tambah Tulisan</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Style khusus builder: presisi drag/posisi lebih jelas sebagai CSS daripada utility classes --}}
    <style>
        .cert-builder{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;margin-top:10px}
        .cert-canvas-wrap{position:sticky;top:24px}
        .cert-canvas{
            position:relative;width:100%;aspect-ratio:1600/1130;
            background-size:cover;background-position:center;
            border:2px solid var(--color-line);border-radius:1.125rem;overflow:hidden;
            box-shadow:var(--shadow-elder);
        }
        .cert-token{
            position:absolute;transform:translate(-50%,-50%);
            padding:5px 11px;border-radius:6px;
            background:rgba(255,255,255,.72);
            border:1.5px dashed var(--color-blue);
            cursor:grab;user-select:none;white-space:nowrap;
            line-height:1.25;display:flex;flex-direction:column;gap:1px;
        }
        .cert-token-label{
            font-size:10px;font-weight:800;letter-spacing:.03em;
            color:var(--color-blue-dark);text-transform:uppercase;
            background:#fff;padding:1px 5px;border-radius:4px;
            align-self:flex-start;
        }
        .cert-token.align-center .cert-token-label{align-self:center}
        .cert-token.align-right .cert-token-label{align-self:flex-end}
        .cert-token-sample{font-size:inherit;color:inherit;font-weight:inherit}
        .cert-token.align-left{transform:translateY(-50%)}
        .cert-token.align-right{transform:translate(-100%,-50%)}
        .cert-token.dragging{cursor:grabbing;background:rgba(255,255,255,.92);z-index:10}
        .cert-token.is-hidden{opacity:.3}
        .cert-token.selected{outline:3px solid var(--color-blue-dark)}
        .cert-canvas-error{
            position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
            text-align:center;padding:30px;background:#fff3f0;color:var(--color-red);
            font-weight:700;font-size:15px;line-height:1.6;z-index:5;
        }
        .cert-canvas-error code{background:#fff;padding:2px 6px;border-radius:4px;font-size:13px}
        .cert-panel{background:#fff;border:2px solid var(--color-line);border-radius:1.125rem;padding:20px;max-height:80vh;overflow:auto}
        .cert-field-row{border:2px solid var(--color-line);border-radius:0.75rem;padding:14px;margin-bottom:14px}
        .cert-field-row.active{border-color:var(--color-blue)}
        .cert-mini-label{display:grid;gap:5px;font-size:14px;font-weight:700;margin-top:8px}
        .cert-mini-label input,.cert-mini-label select{font:inherit;padding:8px 10px;border:2px solid var(--color-line-strong);border-radius:8px;font-size:14px;min-height:auto}
        @media(max-width:1000px){.cert-builder{grid-template-columns:1fr}.cert-canvas-wrap{position:static}}
    </style>

    <script>
    (function(){
        const canvas = document.getElementById('cert-canvas');
        if(!canvas) return;
        const form = document.getElementById('certificate-form');
        const holder = document.getElementById('fields-data-holder');
        const rows = Array.from(document.querySelectorAll('.cert-field-row'));
        const tokens = Array.from(document.querySelectorAll('.cert-token'));
        // Skala huruf mengikuti lebar halaman PDF (A4 landscape = 297 mm ≈ 1122,5 px @96dpi), bukan lebar gambar latar,
        // supaya ukuran huruf di kanvas sama proporsinya dengan hasil PDF.
        const bgWidth = 1122.5;

        // Kode otomatis pada isi tulisan: {TAHUN} dan {URUT} diganti contoh (nomor peserta berikutnya).
        const sampleYear = canvas.dataset.sampleYear || String(new Date().getFullYear());
        const sampleSeq = String(parseInt(canvas.dataset.sampleSeq, 10) || 1).padStart(4, '0');
        const fillTokens = (text) => String(text || '').replace(/\{\s*(TAHUN|URUT)\s*\}/gi, (_, k) => k.toUpperCase() === 'TAHUN' ? sampleYear : sampleSeq);

        const bgTest = new Image();
        bgTest.onerror = () => {
            const err = document.getElementById('cert-canvas-error');
            if (err) err.hidden = false;
        };
        bgTest.src = canvas.dataset.bgSrc;

        function applyFontScale(){
            const scale = canvas.getBoundingClientRect().width / bgWidth;
            tokens.forEach(token => {
                const original = parseFloat(token.dataset.fontsize) || 16;
                token.style.fontSize = Math.max(6, original * scale) + 'px';
            });
        }
        applyFontScale();
        requestAnimationFrame(applyFontScale);
        window.addEventListener('resize', applyFontScale);

        let dragging = null;
        tokens.forEach(token => {
            token.addEventListener('pointerdown', e => {
                dragging = token;
                token.classList.add('dragging');
                token.setPointerCapture(e.pointerId);
                highlightRow(token.dataset.index);
            });
            token.addEventListener('pointermove', e => {
                if (dragging !== token) return;
                const rect = canvas.getBoundingClientRect();
                let x = ((e.clientX - rect.left) / rect.width) * 100;
                let y = ((e.clientY - rect.top) / rect.height) * 100;
                x = Math.max(0, Math.min(100, x));
                y = Math.max(0, Math.min(100, y));
                token.style.left = x + '%';
                token.style.top = y + '%';
                token.dataset.x = x.toFixed(2);
                token.dataset.y = y.toFixed(2);
            });
            token.addEventListener('pointerup', e => {
                if (dragging === token) {
                    token.classList.remove('dragging');
                    token.releasePointerCapture(e.pointerId);
                    dragging = null;
                }
            });
            token.addEventListener('click', () => highlightRow(token.dataset.index));
        });

        function highlightRow(index){
            rows.forEach(r => r.classList.toggle('active', r.dataset.index === index));
            const row = rows.find(r => r.dataset.index === index);
            if (row) row.scrollIntoView({block:'nearest', behavior:'smooth'});
        }

        rows.forEach(row => {
            const idx = row.dataset.index;
            const token = tokens.find(t => t.dataset.index === idx);
            if (!token) return;

            const fontsize = row.querySelector('.f-fontsize');
            const color = row.querySelector('.f-color');
            const align = row.querySelector('.f-align');
            const bold = row.querySelector('.f-bold');
            const visible = row.querySelector('.f-visible');

            fontsize?.addEventListener('input', () => {
                token.dataset.fontsize = fontsize.value;
                applyFontScale();
            });
            color?.addEventListener('input', () => token.style.color = color.value);
            align?.addEventListener('change', () => {
                token.classList.remove('align-left','align-center','align-right');
                token.classList.add('align-' + align.value);
                token.style.textAlign = align.value;
            });
            bold?.addEventListener('change', () => token.style.fontWeight = bold.checked ? '700' : '400');
            visible?.addEventListener('change', () => token.classList.toggle('is-hidden', !visible.checked));

            const valueInput = row.querySelector('.f-value');
            const sampleSpan = token.querySelector('.cert-token-sample');
            valueInput?.addEventListener('input', () => {
                if (sampleSpan) sampleSpan.textContent = fillTokens(valueInput.value) || row.querySelector('b').textContent.trim();
            });
        });

        form.addEventListener('submit', () => {
            holder.innerHTML = '';
            rows.forEach(row => {
                const idx = row.dataset.index;
                const token = tokens.find(t => t.dataset.index === idx);
                const key = token.dataset.key;
                const label = row.querySelector('b').textContent.trim();
                const kind = row.querySelector('.f-remove-form') ? 'custom' : 'dynamic';
                const valueInput = row.querySelector('.f-value');
                const x = token.dataset.x || parseFloat(token.style.left);
                const y = token.dataset.y || parseFloat(token.style.top);

                const fieldsMap = {
                    key, label, kind,
                    x, y,
                    font_size: row.querySelector('.f-fontsize').value,
                    color: row.querySelector('.f-color').value,
                    align: row.querySelector('.f-align').value,
                    bold: row.querySelector('.f-bold').checked ? 1 : 0,
                    visible: row.querySelector('.f-visible').checked ? 1 : 0,
                    value: valueInput ? valueInput.value : '',
                };

                Object.entries(fieldsMap).forEach(([name, val]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `fields[${idx}][${name}]`;
                    input.value = val;
                    holder.appendChild(input);
                });

                const sourceInput = document.createElement('input');
                sourceInput.type = 'hidden';
                sourceInput.name = `fields[${idx}][source]`;
                sourceInput.value = token.dataset.source || '';
                holder.appendChild(sourceInput);
            });
        });
    })();
    </script>
@push('scripts')
<script>
(function () {
    var form = document.getElementById('number-form');
    if (!form) return;
    var input = document.getElementById('number-template');
    var out = document.getElementById('number-example');
    var year = form.getAttribute('data-year');
    var seq = String(parseInt(form.getAttribute('data-seq'), 10) || 1);
    while (seq.length < 4) seq = '0' + seq;

    function render() {
        var text = input.value.replace(/\{\s*(TAHUN|URUT)\s*\}/gi, function (_, k) { return k.toUpperCase() === 'TAHUN' ? year : seq; });
        out.textContent = text.trim() === '' ? '—' : text;
    }
    input.addEventListener('input', render);

    [].forEach.call(form.querySelectorAll('[data-insert]'), function (btn) {
        btn.addEventListener('click', function () {
            var code = btn.getAttribute('data-insert');
            var start = input.selectionStart === null ? input.value.length : input.selectionStart;
            var end = input.selectionEnd === null ? start : input.selectionEnd;
            input.value = input.value.slice(0, start) + code + input.value.slice(end);
            input.focus();
            input.setSelectionRange(start + code.length, start + code.length);
            render();
        });
    });
    render();
})();
</script>
@endpush
@endsection
