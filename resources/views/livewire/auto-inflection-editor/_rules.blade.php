@php
    if ($tabPatternName == 'native') {
        $ruleList = "ruleForm.rules.{$tabTargetName}.{$tabPatternName}['" . (string)$neography->id . "'].rules";
        $inputStyle = "tollerus_{$neography->machine_name}";
    } else {
        $ruleList = "ruleForm.rules.{$tabTargetName}.{$tabPatternName}";
        $inputStyle = '';
    }
@endphp
<x-tollerus::pane class="flex flex-col gap-4 items-start">
    <x-tollerus::alert type="info">{!! Str::markdown(__('tollerus::ui.regex_description', ['regex_url' => 'https://en.wikipedia.org/wiki/Regular_expression'])) !!}</x-tollerus::alert>
    <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
        <template x-for="([ruleId, rule], i) in $store.reorderFunctions.sortItems({{ $ruleList }})">
            <div
                x-bind:id="'rule_' + ruleId"
                data-obj="rule"
                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                x-bind:style="'order: '+i"
                @transitionend="$nextTick(() => {animating=false});"
            >
                <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                    <x-tollerus::inputs.button
                        type="inverse"
                        title="{{ __('tollerus::ui.move_rule_up') }}"
                        x-bind:disabled="animating || $store.reorderFunctions.isFirstItem({{ $ruleList }}, ruleId)"
                        @click="animating=true; moveRule(
                            {{ $ruleList }},
                            $el.closest('[data-obj=&quot;rule&quot;]'),
                            '{{ $tabTargetName }}',
                            '{{ $tabPatternName }}',
                            '{{ $tabPatternName=='native' ? (string)$neography->id : '' }}',
                            ruleId,
                            -1
                        );"
                    >
                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                        <span class="sr-only">{{ __('tollerus::ui.move_rule_up') }}</span>
                    </x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        type="inverse"
                        title="{{ __('tollerus::ui.move_rule_down') }}"
                        x-bind:disabled="animating || $store.reorderFunctions.isLastItem({{ $ruleList }}, ruleId)"
                        @click="animating=true; moveRule(
                            {{ $ruleList }},
                            $el.closest('[data-obj=&quot;rule&quot;]'),
                            '{{ $tabTargetName }}',
                            '{{ $tabPatternName }}',
                            '{{ $tabPatternName=='native' ? (string)$neography->id : '' }}',
                            ruleId,
                            +1
                        );"
                    >
                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                        <span class="sr-only">{{ __('tollerus::ui.move_rule_down') }}</span>
                    </x-tollerus::inputs.button>
                </x-tollerus::panel>
                <x-tollerus::panel class="flex flex-col sm:flex-row-reverse gap-4 items-stretch flex-grow rounded-l-none">
                    <div class="flex flex-row sm:flex-col justify-end sm:justify-start w-auto">
                        <x-tollerus::inputs.button
                            type="inverse"
                            size="small"
                            class="align-middle"
                            title="{{ __('tollerus::ui.delete_rule') }}"
                            @click="$dispatch('open-modal', {
                                message: msgs['delete_rule_confirmation'],
                                buttons: [
                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'rule-delete', payload: {ruleId: ruleId} }
                                ]
                            });"
                        >
                            <x-tollerus::icons.delete/>
                            <label class="sr-only">{{ __('tollerus::ui.delete_rule') }}</label>
                        </x-tollerus::inputs.button>
                    </div>
                    <div class="grid grid-cols-2 gap-2 w-full">
                        @switch ($tabPatternName)
                            @case ('phonemic')
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center" data-keyboard-elem="territory">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_pattern'"
                                        model="rule.pattern"
                                        fieldName="{{ __('tollerus::ui.regex_pattern') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'pattern', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    >
                                        <x-slot:before>
                                            <div
                                                x-data="{ showKeyboard: false }"
                                                class="relative"
                                                @close-virtual-keyboard.window="showKeyboard=false;"
                                            >
                                                <x-tollerus::inputs.button
                                                    x-cloak x-show="!showKeyboard"
                                                    type="secondary"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                    @click="
                                                        editing=true;
                                                        $nextTick(()=>{
                                                            showKeyboard=true;
                                                            $store.virtualKeyboard.mount({
                                                                virtualKeyboardType: 'phonemic',
                                                                mountPoint: $el.parentNode,
                                                                inputFieldId: id
                                                            });
                                                        });
                                                    "
                                                >
                                                    <x-tollerus::icons.keyboard/>
                                                    <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                </x-tollerus::inputs.button>
                                                <x-tollerus::inputs.button
                                                    x-cloak x-show="showKeyboard"
                                                    type="primary"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                    @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                >
                                                    <x-tollerus::icons.keyboard/>
                                                    <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                </x-tollerus::inputs.button>
                                            </div>
                                        </x-slot:before>
                                    </x-tollerus::inputs.text-saveable>
                                </div>
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center" data-keyboard-elem="territory">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_replacement'"
                                        model="rule.replacement"
                                        fieldName="{{ __('tollerus::ui.replace_with') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'replacement', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    >
                                        <x-slot:before>
                                            <div
                                                x-data="{ showKeyboard: false }"
                                                class="relative"
                                                @close-virtual-keyboard.window="showKeyboard=false;"
                                            >
                                                <x-tollerus::inputs.button
                                                    x-cloak x-show="!showKeyboard"
                                                    type="secondary"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                    @click="
                                                        editing=true;
                                                        $nextTick(()=>{
                                                            showKeyboard=true;
                                                            $store.virtualKeyboard.mount({
                                                                virtualKeyboardType: 'phonemic',
                                                                mountPoint: $el.parentNode,
                                                                inputFieldId: id
                                                            });
                                                        });
                                                    "
                                                >
                                                    <x-tollerus::icons.keyboard/>
                                                    <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                </x-tollerus::inputs.button>
                                                <x-tollerus::inputs.button
                                                    x-cloak x-show="showKeyboard"
                                                    type="primary"
                                                    size="small"
                                                    class="align-middle"
                                                    title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                    @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                >
                                                    <x-tollerus::icons.keyboard/>
                                                    <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                </x-tollerus::inputs.button>
                                            </div>
                                        </x-slot:before>
                                    </x-tollerus::inputs.text-saveable>
                                </div>
                            @break
                            @case ('native')
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center" data-keyboard-elem="territory">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_pattern'"
                                        model="rule.pattern"
                                        fieldName="{{ __('tollerus::ui.regex_pattern') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'pattern', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    >
                                        <x-slot:before>
                                            @if ($neography->keyboards()->exists() > 0)
                                                <div
                                                    x-data="{ showKeyboard: false }"
                                                    class="relative"
                                                    @close-virtual-keyboard.window="showKeyboard=false;"
                                                >
                                                    <x-tollerus::inputs.button
                                                        x-cloak x-show="!showKeyboard"
                                                        type="secondary"
                                                        size="small"
                                                        class="align-middle"
                                                        title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                        @click="
                                                            editing=true;
                                                            $nextTick(()=>{
                                                                showKeyboard=true;
                                                                $store.virtualKeyboard.mount({
                                                                    virtualKeyboardType: 'native',
                                                                    neographySubset: ['{{ (string)$neography->id }}'],
                                                                    mountPoint: $el.parentNode,
                                                                    inputFieldId: id
                                                                });
                                                            });
                                                        "
                                                    >
                                                        <x-tollerus::icons.keyboard/>
                                                        <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        x-cloak x-show="showKeyboard"
                                                        type="primary"
                                                        size="small"
                                                        class="align-middle"
                                                        title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                        @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                    >
                                                        <x-tollerus::icons.keyboard/>
                                                        <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                    </x-tollerus::inputs.button>
                                                </div>
                                            @endif
                                        </x-slot:before>
                                    </x-tollerus::inputs.text-saveable>
                                </div>
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center" data-keyboard-elem="territory">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_replacement'"
                                        model="rule.replacement"
                                        fieldName="{{ __('tollerus::ui.replace_with') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'replacement', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    >
                                        <x-slot:before>
                                            @if ($neography->keyboards()->exists())
                                                <div
                                                    x-data="{ showKeyboard: false }"
                                                    class="relative"
                                                    @close-virtual-keyboard.window="showKeyboard=false;"
                                                >
                                                    <x-tollerus::inputs.button
                                                        x-cloak x-show="!showKeyboard"
                                                        type="secondary"
                                                        size="small"
                                                        class="align-middle"
                                                        title="{{ __('tollerus::ui.show_virtual_keyboard') }}"
                                                        @click="
                                                            editing=true;
                                                            $nextTick(()=>{
                                                                showKeyboard=true;
                                                                $store.virtualKeyboard.mount({
                                                                    virtualKeyboardType: 'native',
                                                                    neographySubset: ['{{ (string)$neography->id }}'],
                                                                    mountPoint: $el.parentNode,
                                                                    inputFieldId: id
                                                                });
                                                            });
                                                        "
                                                    >
                                                        <x-tollerus::icons.keyboard/>
                                                        <label class="sr-only">{{ __('tollerus::ui.show_virtual_keyboard') }}</label>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        x-cloak x-show="showKeyboard"
                                                        type="primary"
                                                        size="small"
                                                        class="align-middle"
                                                        title="{{ __('tollerus::ui.hide_virtual_keyboard') }}"
                                                        @click="showKeyboard=false; $store.virtualKeyboard.unmount();"
                                                    >
                                                        <x-tollerus::icons.keyboard/>
                                                        <label class="sr-only">{{ __('tollerus::ui.hide_virtual_keyboard') }}</label>
                                                    </x-tollerus::inputs.button>
                                                </div>
                                            @endif
                                        </x-slot:before>
                                    </x-tollerus::inputs.text-saveable>
                                </div>
                            @break
                            @default
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_pattern'"
                                        model="rule.pattern"
                                        fieldName="{{ __('tollerus::ui.regex_pattern') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'pattern', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    />
                                </div>
                                <div class="col-span-2 lg:col-span-1 flex flex-col justify-center">
                                    <x-tollerus::inputs.text-saveable
                                        idExpression="'rule_' + ruleId + '_replacement'"
                                        model="rule.replacement"
                                        fieldName="{{ __('tollerus::ui.replace_with') }}"
                                        showLabel="true"
                                        saveEvent="$wire.updateRule(ruleId, 'replacement', document.getElementById(id).value, id);"
                                        class="{{ $inputStyle }}"
                                    />
                                </div>
                            @break
                        @endswitch
                    </div>
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
