@extends('layouts.app')
@section('content')
<div class="admin-shell narrow-wide">
    <div class="dashboard-head">
        <div>
            <div class="eyebrow">ELDERWISE · ADMIN</div>
            <h1>Pengaturan Sertifikat</h1>
            <p>Unggah gambar latar sertifikat, lalu geser tiap tulisan ke posisi yang diinginkan.</p>
        </div>
        <a class="btn btn-ghost" href="{{ route('admin.dashboard') }}">← Kembali ke Admin</a>
    </div>

    @if($errors->any())
        <div class="alert error-box">
            <b>Periksa kembali isian berikut:</b>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- ===================== UPLOAD LATAR ===================== --}}
    <div class="admin-section-head">
        <div><h2>1. Gambar Latar Sertifikat</h2><p>Format JPG/PNG, ukuran landscape disarankan (misal 1600×1130 px).</p></div>
    </div>
    <div class="table-card" style="padding:24px">
        <form method="POST" action="{{ route('admin.certificate.background') }}" enctype="multipart/form-data" class="form-stack" style="max-width:520px">
            @csrf
            <label>Pilih gambar latar baru
                <input type="file" name="background" accept="image/png,image/jpeg" required>
            </label>
            @error('background')<div class="error">{{ $message }}</div>@enderror
            <button class="btn btn-primary">Unggah Gambar</button>
        </form>
        @if($setting->background_path)
            <p class="pdf-current">✓ Gambar latar aktif: {{ basename($setting->background_path) }} ({{ $setting->background_width }}×{{ $setting->background_height }}px)</p>
        @else
            <p class="muted">Belum ada gambar latar. Unggah dulu sebelum mengatur posisi tulisan.</p>
        @endif
    </div>

    @if($setting->background_path)
    {{-- ===================== BUILDER POSISI ===================== --}}
    <div class="admin-section-head">
        <div>
            <h2>2. Atur Posisi Tulisan</h2>
            <p>Geser setiap kotak ke tempat yang sesuai di atas gambar. Teks yang tampil adalah <b>contoh isi</b>, supaya Bapak/Ibu bisa melihat perkiraan hasil aslinya.</p>
        </div>
        <a href="{{ route('admin.certificate.preview') }}" target="_blank" class="btn btn-ghost">🔍 Lihat Pratinjau PDF</a>
    </div>

    <form method="POST" action="{{ route('admin.certificate.fields') }}" id="certificate-form">
        @csrf
        <input type="hidden" name="title" value="{{ $setting->title }}">
        <div id="fields-data-holder"></div>

        <div class="cert-builder">
            <div class="cert-canvas-wrap">
                <div class="cert-canvas" id="cert-canvas" data-bg-width="{{ $setting->background_width ?? 1600 }}" style="background-image:url('{{ Storage::url($setting->background_path) }}')">
                    @foreach($fields as $i => $f)
                        @php
                            $sampleText = match($f['source'] ?? null) {
                                'user_name' => $sample['user_name'],
                                'issued_date' => $sample['issued_date'],
                                'certificate_number' => $sample['certificate_number'],
                                default => $f['value'] ?: $f['label'],
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
                <p class="cert-hint">Seret kotak teks ke posisi yang diinginkan. Ukuran huruf di sini mengikuti skala asli, dan teks yang tampil adalah contoh isi supaya mudah dicek apakah pas atau kepotong. Klik "Lihat Pratinjau PDF" untuk melihat hasil sebenarnya.</p>
            </div>

            <div class="cert-panel">
                <h3>Daftar Tulisan</h3>
                <div id="cert-field-list">
                    @foreach($fields as $i => $f)
                        <div class="cert-field-row" data-index="{{ $i }}">
                            <div class="cert-field-row-head">
                                <label class="checkbox-row" style="gap:8px">
                                    <input type="checkbox" class="f-visible" {{ ($f['visible'] ?? true) ? 'checked':'' }}>
                                    <span><b>{{ $f['label'] }}</b></span>
                                </label>
                                @if(($f['kind'] ?? 'dynamic') === 'custom')
                                    <form method="POST" action="{{ route('admin.certificate.fields.remove', $f['key']) }}" class="f-remove-form" onsubmit="return confirm('Hapus tulisan ini dari sertifikat?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-danger">Hapus</button>
                                    </form>
                                @else
                                    <span class="tag">bawaan</span>
                                @endif
                            </div>

                            @if(($f['kind'] ?? 'dynamic') === 'custom' || $f['source'] === 'program_name')
                                <label class="cert-mini-label">Teks / isi
                                    <input type="text" class="f-value" value="{{ $f['value'] }}" maxlength="500">
                                </label>
                            @else
                                <p class="cert-mini-note">Isi otomatis: {{ match($f['source']) { 'user_name'=>'nama peserta yang login', 'issued_date'=>'tanggal sertifikat dibuat', 'certificate_number'=>'nomor sertifikat unik', default=>'-' } }} <br>Contoh: <i>{{ match($f['source']) { 'user_name'=>$sample['user_name'], 'issued_date'=>$sample['issued_date'], 'certificate_number'=>$sample['certificate_number'], default=>'-' } }}</i></p>
                            @endif

                            <div class="form-grid-2">
                                <label class="cert-mini-label">Ukuran huruf
                                    <input type="number" class="f-fontsize" value="{{ $f['font_size'] }}" min="8" max="120">
                                </label>
                                <label class="cert-mini-label">Warna
                                    <input type="color" class="f-color" value="{{ $f['color'] }}">
                                </label>
                            </div>
                            <label class="cert-mini-label">Perataan teks
                                <select class="f-align">
                                    <option value="left" {{ $f['align']==='left'?'selected':'' }}>Kiri</option>
                                    <option value="center" {{ $f['align']==='center'?'selected':'' }}>Tengah</option>
                                    <option value="right" {{ $f['align']==='right'?'selected':'' }}>Kanan</option>
                                </select>
                            </label>
                            <label class="checkbox-row" style="gap:8px;margin-top:6px">
                                <input type="checkbox" class="f-bold" {{ !empty($f['bold']) ? 'checked':'' }}>
                                <span>Huruf tebal</span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary full" style="margin-top:18px">Simpan Pengaturan Sertifikat</button>
            </div>
        </div>
    </form>

    {{-- ===================== TAMBAH FIELD CUSTOM ===================== --}}
    <div class="admin-section-head">
        <div><h2>3. Tambah Tulisan Baru</h2><p>Misalnya "Diselenggarakan oleh", "Durasi Pelatihan", atau nama penandatangan.</p></div>
    </div>
    <div class="table-card" style="padding:24px">
        <form method="POST" action="{{ route('admin.certificate.fields.add') }}" class="form-grid-2" style="align-items:end">
            @csrf
            <label>Nama tulisan (label)
                <input type="text" name="label" placeholder="Contoh: Diselenggarakan oleh" required maxlength="120">
            </label>
            <label>Isi teks
                <input type="text" name="value" placeholder="Contoh: Politeknik Negeri Jakarta" maxlength="500">
            </label>
            <button class="btn btn-ghost" style="grid-column:span 2">+ Tambah Tulisan</button>
        </form>
    </div>
    @endif
</div>

<style>
.cert-builder{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;margin-top:10px}
.cert-canvas-wrap{position:sticky;top:100px}
.cert-canvas{
    position:relative;width:100%;aspect-ratio:1600/1130;
    background-size:cover;background-position:center;
    border:2px solid var(--line);border-radius:var(--radius-lg);overflow:hidden;
    box-shadow:var(--shadow);
}
.cert-token{
    position:absolute;transform:translate(-50%,-50%);
    padding:5px 11px;border-radius:6px;
    background:rgba(255,255,255,.72);
    border:1.5px dashed var(--blue);
    cursor:grab;user-select:none;white-space:nowrap;
    line-height:1.25;display:flex;flex-direction:column;gap:1px;
}
.cert-token-label{
    font-size:10px;font-weight:800;letter-spacing:.03em;
    color:var(--blue-dark);text-transform:uppercase;
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
.cert-token.selected{outline:3px solid var(--blue-dark)}
.cert-hint{margin-top:12px;color:var(--muted);font-size:14px}
.cert-panel{background:#fff;border:2px solid var(--line);border-radius:var(--radius-lg);padding:20px;max-height:80vh;overflow:auto}
.cert-panel h3{margin-top:0}
.cert-field-row{border:2px solid var(--line);border-radius:var(--radius-md);padding:14px;margin-bottom:14px}
.cert-field-row.active{border-color:var(--blue)}
.cert-field-row-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.cert-mini-label{display:grid;gap:5px;font-size:14px;font-weight:700;margin-top:8px}
.cert-mini-label input,.cert-mini-label select{font:inherit;padding:8px 10px;border:2px solid var(--line-strong);border-radius:8px;font-size:14px;min-height:auto}
.cert-mini-note{font-size:13px;color:var(--muted);margin:6px 0 0}
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
    const bgWidth = parseFloat(canvas.dataset.bgWidth) || 1600;

    // --- Skala ukuran huruf supaya PROPORSIONAL dengan ukuran asli gambar ---
    // font_size disimpan dalam px relatif terhadap lebar gambar asli (mis. 1600px).
    // Di layar, kanvas bisa tampil lebih kecil/besar tergantung ukuran jendela,
    // jadi ukuran huruf di kanvas builder harus ikut diskalakan supaya
    // perbandingannya SAMA seperti nanti di PDF.
    function applyFontScale(){
        const scale = canvas.getBoundingClientRect().width / bgWidth;
        tokens.forEach(token => {
            const original = parseFloat(token.dataset.fontsize) || 16;
            token.style.fontSize = Math.max(6, original * scale) + 'px';
        });
    }
    applyFontScale();
    requestAnimationFrame(applyFontScale); // jaga-jaga jika CSS (aspect-ratio) belum selesai diterapkan saat load pertama
    window.addEventListener('resize', applyFontScale);

    // --- Drag and drop di kanvas ---
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

    // --- Sinkronisasi panel kontrol -> token visual ---
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

        // Saat admin mengetik isi teks custom (atau nama program), preview di kanvas ikut berubah live
        const valueInput = row.querySelector('.f-value');
        const sampleSpan = token.querySelector('.cert-token-sample');
        valueInput?.addEventListener('input', () => {
            if (sampleSpan) sampleSpan.textContent = valueInput.value || row.querySelector('b').textContent.trim();
        });
    });

    // --- Susun ulang data field jadi input tersembunyi sebelum submit ---
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

            // source field bawaan tidak diedit dari sini — dikirim via data attribute
            const sourceInput = document.createElement('input');
            sourceInput.type = 'hidden';
            sourceInput.name = `fields[${idx}][source]`;
            sourceInput.value = token.dataset.source || '';
            holder.appendChild(sourceInput);
        });
    });
})();
</script>
@endsection
