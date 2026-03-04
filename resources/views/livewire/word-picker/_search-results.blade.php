@foreach ($theseResults as $result)
    <button
        @class([
            'py-1 pr-4 flex flex-row gap-2 justify-start items-center font-bold cursor-pointer bg-white dark:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed',
            'hover:bg-zinc-100 hover:dark:bg-zinc-700' => !$requireForm || ($result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Form),
            'pl-4' => $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Glyph || $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry,
            'pl-12' => $result['kind'] == \PeterMarkley\Tollerus\Enums\GlobalIdKind::Form,
        ])
        @if ($requireForm && ($result['kind'] != \PeterMarkley\Tollerus\Enums\GlobalIdKind::Form))
            disabled
        @endif
        @click="open=false; $wire.selectWord('{{ $result['globalId'] }}');"
    >
        @switch($result['kind'])
            @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Glyph)
                <x-tollerus::icons.micro.neography class="shrink-0" />
            @break
            @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Entry)
                <x-tollerus::icons.micro.entries class="shrink-0" />
            @break
            @case(\PeterMarkley\Tollerus\Enums\GlobalIdKind::Form)
                <x-tollerus::icons.micro.fingerprint class="shrink-0" />
            @break
        @endswitch
        <span class="font-bold whitespace-nowrap shrink-0">{{ $result['transliterated'] }}</span>
        <span class="whitespace-nowrap shrink-1 tollerus_custom_{{ $result['neographyMachineName'] }}">{{ $result['native'] }}</span>
        <span class="font-mono font-normal shrink-0">{{ $result['globalId'] }}</span>
    </button>
@endforeach
