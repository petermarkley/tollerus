@props([
    'globalId',
])
<div
    x-show="highlight == '{{ $globalId }}'" x-cloak
    class="absolute w-[calc(100%+1rem)] h-[calc(100%+1rem)] left-1/2 top-1/2 -translate-1/2 rounded-[0.5rem] border-2 border-dotted border-tollerus-primary pointer-events-none"
>&nbsp;</div>
