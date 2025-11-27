<x-tollerus::pane class="flex flex-col gap-4 items-start">
    @if ($tabPatternName == 'native')
        <template x-for="(rule, ruleId) in ruleForm.rules.{{ $tabTargetName }}.{{ $tabPatternName }}['{{ (string)$neography->id }}'].rules">
    @else
        <template x-for="(rule, ruleId) in ruleForm.rules.{{ $tabTargetName }}.{{ $tabPatternName }}">
    @endif
        <x-tollerus::panel class="flex flex-col gap-2"><p><code x-text="ruleId"></code></p><pre x-text="JSON.stringify(rule)"></pre></x-tollerus::panel>
    </template>
    <x-tollerus::inputs.missing-data
        size="small"
        title="{{ __('tollerus::ui.add_rule') }}"
        class="relative flex flex-row gap-2 justify-center items-center w-full"
        @click="$wire.createRule('{{ $tabTargetName }}', '{{ $tabPatternName }}', '{{ $tabPatternName=='native' ? (string)$neography->id : '' }}');"
        wire:loading.attr="disabled"
        wire:target="createRule"
    >
        <x-tollerus::icons.plus/>
        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_rule') }}</span>
    </x-tollerus::inputs.missing-data>
</x-tollerus::pane>
