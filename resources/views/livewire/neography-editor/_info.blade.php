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
        <div class="flex flex-col gap-4 items-start">
            @foreach ($writingDirectionOpts as $writingDirection)
                <div class="flex flex-row gap-4 justify-start items-center">
                    <div class="inline-block align-middle w-6 h-6 relative group">
                        <x-tollerus::icons.star
                            x-bind:fill="infoForm.direction_primary == {{ $neography->direction_primary }} ? 'currentColor' : 'none'"
                            class="rounded-lg text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:dark:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700 group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white"
                        />
                        <input
                            type="radio"
                            id="direction_primary_{{ $writingDirection['string'] }}"
                            name="direction_primary"
                            value="{{ $writingDirection['string'] }}"
                            wire:model="infoForm.direction_primary"
                            class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                            @change="btn = 'save'; dirty=true;"
                        />
                    </div>
                    <label for="direction_primary_{{ $writingDirection['string'] }}">{{ $writingDirection['local'] }}</label>
                </div>
            @endforeach
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
