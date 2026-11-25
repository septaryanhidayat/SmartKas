@props(['code'])

@php
    $fullCode = (string) ($code ?? '-');
    $displayCode = format_tx_code($fullCode);
    $isTruncated = $displayCode !== $fullCode;
@endphp

<span x-data="{ copied: false }" class="inline-flex items-center gap-1 max-w-full group">
    <span title="{{ $fullCode }}"
          class="font-mono font-bold text-slate-800 whitespace-nowrap {{ $isTruncated ? 'px-2 py-0.5 rounded-md bg-slate-100 border border-slate-200/80 text-[11px]' : '' }}">
        {{ $displayCode }}
    </span>
    @if($fullCode !== '-' && $fullCode !== '')
        <button type="button"
                @click.prevent="navigator.clipboard?.writeText(@js($fullCode)); copied = true; setTimeout(() => copied = false, 1500)"
                :title="copied ? 'Kode disalin' : 'Salin kode lengkap: ' + @js($fullCode)"
                class="opacity-60 group-hover:opacity-100 p-0.5 rounded text-slate-400 hover:text-teal-700 hover:bg-teal-50 transition cursor-pointer shrink-0">
            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </button>
    @endif
</span>
