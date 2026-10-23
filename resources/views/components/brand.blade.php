@props(['site', 'size' => 44])
{{-- Merek situs: logo unggahan (atau lencana huruf jika belum ada logo) + nama web --}}
<span {{ $attributes->merge(['class' => 'flex items-center gap-3 min-w-0']) }}>
    @if($site->logoUrl())
        <img src="{{ $site->logoUrl() }}" alt="{{ $site->site_name }}" class="w-auto object-contain shrink-0"
             style="height:{{ $size }}px;max-width:{{ $size * 4 }}px">
    @else
        <span class="grid place-items-center rounded-xl bg-blue text-white font-extrabold shrink-0"
              style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.45) }}px" aria-hidden="true">{{ $site->initial() }}</span>
    @endif
    @if(! $site->logoUrl() || $site->show_name_with_logo)
        <span class="font-extrabold text-navy leading-tight truncate">{{ $site->site_name }}</span>
    @endif
</span>
