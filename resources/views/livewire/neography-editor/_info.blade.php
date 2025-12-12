<x-tollerus::panel id="tabpanel-info" role="tabpanel" x-cloak x-show="tab=='info'" class="flex flex-col gap-6">
    <div class="flex justify-start items-start">
        <x-tollerus::inputs.toggle id="visible" model="infoForm.visible" label="{{ __('tollerus::ui.visible') }}" @change="btn = 'save'; dirty=true;" />
    </div>
    <div class="flex flex-col gap-4">
        <h3 class="font-bold text-lg">{{ __('tollerus::ui.name') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-tollerus::inputs.text id="name" model="infoForm.name" label="{{ __('tollerus::ui.human_friendly') }}" @input="btn = 'save'; dirty=true;" />
            <x-tollerus::inputs.text id="machine_name" model="infoForm.machine_name" label="{{ __('tollerus::ui.machine_friendly') }}" @input="btn = 'save'; dirty=true;" />
        </div>
    </div>
    <div class="flex flex-col gap-4">
        <h3 class="font-bold text-lg">{{ __('tollerus::ui.writing_direction') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $radioStyle = "rounded-lg bg-none border-2
                    border-zinc-600 group-has-hover:border-zinc-500 dark:border-zinc-500 group-has-hover:dark:border-zinc-400 group-has-checked:border-cyan-800 group-has-checked:group-has-hover:border-cyan-700 group-has-checked:dark:border-cyan-300 group-has-checked:group-has-hover:dark:border-cyan-200
                    text-zinc-600   group-has-hover:text-zinc-500   dark:text-zinc-500   group-has-hover:dark:text-zinc-400
                    group-has-checked:text-white group-has-checked:group-has-hover:text-white group-has-checked:dark:text-zinc-800 group-has-checked:group-has-hover:dark:text-zinc-800
                    group-has-checked:bg-cyan-800 group-has-checked:group-has-hover:bg-cyan-700 group-has-checked:dark:bg-cyan-300 group-has-checked:group-has-hover:dark:bg-cyan-200
                    group-has-checked:dark:saturate-50
                    group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white
                ";
            @endphp
            <fieldset class="flex flex-col gap-4 items-start">
                <div><legend class="font-bold text-base">{{ __('tollerus::ui.primary') }}</legend></div>
                @foreach ($writingDirectionOpts as $writingDirection)
                    <div class="flex flex-row gap-2 justify-start items-center">
                        <div class="inline-block align-middle w-6 h-6 relative group">
                            @switch ($writingDirection['enum'])
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::LeftToRight)
                                    <x-tollerus::icons.arrow-long-right class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::RightToLeft)
                                    <x-tollerus::icons.arrow-long-left class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::TopToBottom)
                                    <x-tollerus::icons.arrow-long-down class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::BottomToTop)
                                    <x-tollerus::icons.arrow-long-up class="{{ $radioStyle }}" />
                                @break
                            @endswitch
                            <input
                                type="radio"
                                id="direction_primary_{{ $writingDirection['string'] }}"
                                name="direction_primary"
                                value="{{ $writingDirection['string'] }}"
                                wire:model="infoForm.direction_primary"
                                class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                @change="
                                    btn = 'save';
                                    dirty=true;
                                    if (!@js( $writingDirection['secondaryOpts'] ).includes(infoForm.direction_secondary)) {
                                        infoForm.direction_secondary = '{{ $writingDirection['secondaryOpts'][0] }}';
                                    }
                                "
                            />
                        </div>
                        <label for="direction_primary_{{ $writingDirection['string'] }}">{{ $writingDirection['local'] }}</label>
                    </div>
                @endforeach
            </fieldset>
            <fieldset class="flex flex-col gap-4 items-start">
                <div><legend class="font-bold text-base">{{ __('tollerus::ui.secondary') }}</legend></div>
                @foreach ($writingDirectionOpts as $writingDirection)
                    <div
                        x-cloak x-show="@js( $writingDirection['secondaryOpts'] ).includes(infoForm.direction_primary)"
                        class="flex flex-row gap-2 justify-start items-center"
                    >
                        <div class="inline-block align-middle w-6 h-6 relative group">
                            @switch ($writingDirection['enum'])
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::LeftToRight)
                                    <x-tollerus::icons.arrow-long-right class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::RightToLeft)
                                    <x-tollerus::icons.arrow-long-left class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::TopToBottom)
                                    <x-tollerus::icons.arrow-long-down class="{{ $radioStyle }}" />
                                @break
                                @case(\PeterMarkley\Tollerus\Enums\WritingDirection::BottomToTop)
                                    <x-tollerus::icons.arrow-long-up class="{{ $radioStyle }}" />
                                @break
                            @endswitch
                            <input
                                type="radio"
                                id="direction_secondary_{{ $writingDirection['string'] }}"
                                name="direction_secondary"
                                value="{{ $writingDirection['string'] }}"
                                wire:model="infoForm.direction_secondary"
                                class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                @change="btn = 'save'; dirty=true;"
                                x-bind:disabled="!@js( $writingDirection['secondaryOpts'] ).includes(infoForm.direction_primary)"
                            />
                        </div>
                        <label for="direction_secondary_{{ $writingDirection['string'] }}">{{ $writingDirection['local'] }}</label>
                    </div>
                @endforeach
            </fieldset>
        </div>
    </div>
    <div class="flex flex-row justify-start gap-2">
        <x-tollerus::inputs.button type="secondary" x-bind:disabled="!dirty" @click="$wire.refreshInfoForm(); dirty=false;">{{ __('tollerus::ui.reset') }}</x-tollerus::inputs.button>
        <x-tollerus::inputs.button
            @click="btn = 'saving'; $wire.infoSave('',{});"
            x-bind:disabled="!dirty"
            wire:loading.attr="disabled"
            wire:target="infoSave"
            @save-info-success.window="btn = 'saved'; dirty=false; if ($event.detail[0].afterSuccess) {$dispatch($event.detail[0].afterSuccess, $event.detail[0].payload);}"
            @save-info-failure.window="btn = 'save';"
            x-text="msgs[btn]" />
    </div>
</x-tollerus::panel>
