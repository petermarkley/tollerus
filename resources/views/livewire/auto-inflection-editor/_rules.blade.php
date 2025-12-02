@php
    if ($tabPatternName == 'native') {
        $ruleList = "ruleForm.rules.{$tabTargetName}.{$tabPatternName}['" . (string)$neography->id . "'].rules";
    } else {
        $ruleList = "ruleForm.rules.{$tabTargetName}.{$tabPatternName}";
    }
@endphp
<x-tollerus::pane class="flex flex-col gap-4 items-start">
    <div class="flex flex-col gap-4 items-start" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
        <template x-for="(rule, ruleId) in {{ $ruleList }}">
            <div
                x-bind:id="'rule_' + ruleId"
                data-obj="rule"
                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                x-bind:style="'order: '+rule.order"
                @transitionend="$nextTick(() => {animating=false});"
            >
                <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                    <x-tollerus::inputs.button
                        type="inverse"
                        title="{{ __('tollerus::ui.move_rule_up') }}"
                        x-bind:disabled="animating || $store.reorderFunctions.isFirstItem({{ $ruleList }}, ruleId)"
                        @click="animating=true; moveRule({{ $ruleList }}, $el.closest('[data-obj=&quot;rule&quot;]'), '{{ $tabTargetName }}', '{{ $tabPatternName }}', '{{ $tabPatternName=='native' ? (string)$neography->id : '' }}', ruleId, -1);"
                    >
                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                        <span class="sr-only">{{ __('tollerus::ui.move_rule_up') }}</span>
                    </x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        type="inverse"
                        title="{{ __('tollerus::ui.move_rule_down') }}"
                        x-bind:disabled="animating || $store.reorderFunctions.isLastItem({{ $ruleList }}, ruleId)"
                        @click="animating=true; moveRule({{ $ruleList }}, $el.closest('[data-obj=&quot;rule&quot;]'), '{{ $tabTargetName }}', '{{ $tabPatternName }}', '{{ $tabPatternName=='native' ? (string)$neography->id : '' }}', ruleId, +1);"
                    >
                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                        <span class="sr-only">{{ __('tollerus::ui.move_rule_down') }}</span>
                    </x-tollerus::inputs.button>
                </x-tollerus::panel>
                <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                    <p><code x-text="ruleId"></code></p><pre x-text="JSON.stringify(rule)"></pre>
                </x-tollerus::panel>
            </div>
        </template>
    </div>
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
