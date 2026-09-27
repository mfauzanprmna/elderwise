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
</body>
</html>
