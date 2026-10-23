<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    html, body { margin:0; padding:0; }
    .sheet {
        position: relative;
        width: 297mm;   /* A4 landscape */
        height: 210mm;
        overflow: hidden;
    }
    .sheet img.bg {
        position: absolute;
        top: 0; left: 0;
        width: 297mm;
        height: 210mm;
    }
    .field {
        position: absolute;
        font-family: DejaVu Sans, sans-serif;
        white-space: nowrap;
        transform: translateY(-50%);
    }
    .align-center { text-align: center; transform: translate(-50%,-50%); }
    .align-left { text-align: left; }
    .align-right { text-align: right; transform: translate(-100%,-50%); }

    /* ===== Halaman 2: rincian materi ===== */
    .sheet-2 { page-break-before: always; height: 209mm; background: #ffffff; }
    .panel {
        position: absolute;
        left: 6%; top: 6%;
        width: 88%; height: 88%;
        background: #ffffff;
        font-family: DejaVu Sans, sans-serif;
        color: #16233a;
    }
    .panel-inner { padding: 16px 28px; }
    .t-title { text-align: center; font-size: 20px; font-weight: 700; color: #0f2f52; margin: 0 0 2px; }
    .t-sub { text-align: center; font-size: 11px; color: #41546c; margin: 0 0 10px; }
    table.t-info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.t-info td { font-size: 11px; padding: 1px 0; }
    table.t-main { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.t-main th { background: #0f2f52; color: #ffffff; font-size: 11px; text-align: left; padding: 6px 8px; }
    table.t-main td { border-bottom: 1px solid #c9d6e6; padding: 4px 8px; vertical-align: top; }
    table.t-main tr.t-total td { border-top: 2px solid #0f2f52; border-bottom: none; font-weight: 700; background: #f4f7fb; }
    .t-note { font-size: 10px; color: #41546c; margin-top: 8px; }
</style>
</head>
<body>
<div class="sheet">
    @if($backgroundPath && file_exists($backgroundPath))
        <img class="bg" src="{{ $backgroundPath }}">
    @endif

    @foreach($fields as $f)
        @if(($f['visible'] ?? true))
            @php
                $text = match($f['source'] ?? null) {
                    'user_name' => $values['user_name'],
                    'issued_date' => $values['issued_date'],
                    'certificate_number' => $values['certificate_number'],
                    default => $f['value'] ?? '',
                };
                if (empty($text) && ($f['source'] ?? null) === 'program_name') {
                    $text = $f['value'] ?? '';
                }
                $alignClass = match($f['align'] ?? 'center') {
                    'left' => 'align-left',
                    'right' => 'align-right',
                    default => 'align-center',
                };
            @endphp
            @if(filled($text))
                <div class="field {{ $alignClass }}"
                     style="left:{{ $f['x'] }}%; top:{{ $f['y'] }}%;
                            font-size:{{ $f['font_size'] ?? 16 }}px;
                            color:{{ $f['color'] ?? '#15304d' }};
                            font-weight:{{ !empty($f['bold']) ? '700' : '400' }};">
                    {{ $text }}
                </div>
            @endif
        @endif
    @endforeach
</div>

@if(!empty($transcript))
@php $fs = $transcript['font_px']; @endphp
<div class="sheet sheet-2">
    <div class="panel">
        <div class="panel-inner">
            <p class="t-title">Rincian Materi Pelatihan</p>
            <p class="t-sub">{{ $setting->title ?: 'Sertifikat Kelulusan' }}</p>

            <table class="t-info">
                <tr>
                    <td style="width:46%"><b>Nama</b> : {{ $values['user_name'] }}</td>
                    <td style="width:30%"><b>No. Sertifikat</b> : {{ $values['certificate_number'] }}</td>
                    <td style="width:24%; text-align:right"><b>Tanggal</b> : {{ $values['issued_date'] }}</td>
                </tr>
            </table>

            <table class="t-main">
                <thead>
                    <tr>
                        <th style="width:6%">No</th>
                        <th style="width:26%">Modul</th>
                        <th style="width:58%">Materi yang Dipelajari</th>
                        <th style="width:10%; text-align:center">JP</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($transcript['rows'] as $i => $row)
                    <tr>
                        <td style="font-size:{{ $fs }}px">{{ $i + 1 }}</td>
                        <td style="font-size:{{ $fs }}px"><b>{{ $row['title'] }}</b>@if($row['slogan'])<br><span style="font-size:{{ max(8, $fs - 2) }}px; color:#41546c">{{ $row['slogan'] }}</span>@endif</td>
                        <td style="font-size:{{ $fs }}px">@if($transcript['compact']){{ count($row['topics']) }} materi @else{{ $row['topics'] ? implode(' • ', $row['topics']) : $row['description'] }}@endif</td>
                        <td style="font-size:{{ $fs }}px; text-align:center">{{ $row['jp'] }}</td>
                    </tr>
                @endforeach
                    @if(($transcript['hidden_count'] ?? 0) > 0)
                    <tr>
                        <td colspan="4" style="font-size:{{ $fs }}px; color:#41546c; text-align:center">… dan {{ $transcript['hidden_count'] }} modul lainnya</td>
                    </tr>
                    @endif
                    <tr class="t-total">
                        <td colspan="3" style="font-size:{{ $fs + 1 }}px; text-align:right">Total Jam Pelatihan</td>
                        <td style="font-size:{{ $fs + 1 }}px; text-align:center">{{ $transcript['total_jp'] }} JP</td>
                    </tr>
                </tbody>
            </table>
            <p class="t-note">JP = Jam Pelatihan. Setiap modul dihitung 1 JP.</p>
        </div>
    </div>
</div>
@endif
</body>
</html>
