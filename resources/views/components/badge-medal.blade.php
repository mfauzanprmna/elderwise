@props(['module', 'state' => 'locked', 'size' => 56])
@php
    // state: done = medali berwarna penuh, progress = sedang dikerjakan, locked = belum terbuka
    $c = $module->badgeColor();
    $style = match ($state) {
        'done' => "background:linear-gradient(135deg,{$c['from']},{$c['to']});border-color:{$c['border']};",
        'progress' => 'background:var(--color-blue-tint);border-color:var(--color-blue);',
        default => 'background:var(--color-bg);border-color:var(--color-line);',
    };
    $img = $module->badgeImageUrl();
@endphp
<div {{ $attributes->merge(['class' => 'rounded-full grid place-items-center shrink-0 border-2 overflow-hidden ' . ($state === 'locked' ? 'grayscale opacity-45' : '') . ($state === 'done' ? ' shadow-elder' : '')]) }}
     style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.45) }}px;{{ $style }}">
    @if($img)
        <img src="{{ $img }}" alt="" class="w-[68%] h-[68%] object-contain">
    @else
        <span aria-hidden="true">{{ $module->badgeIcon() }}</span>
    @endif
</div>
