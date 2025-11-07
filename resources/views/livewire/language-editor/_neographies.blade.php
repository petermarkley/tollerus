<x-tollerus::panel
    id="tabpanel-neographies"
    role="tabpanel"
    x-cloak x-show="tab=='neographies'"
    class="flex flex-col gap-6 items-start"
>
    @if (count($neographies)>0)
        <x-tollerus::alert>
            <p>{{ __('tollerus::ui.language_neographies_context_notice', ['language' => $language->name]) }} <a href="{{ route('tollerus.admin.neographies.index') }}">{{ __('tollerus::ui.edit_all_neographies') }}</a></p>
        </x-tollerus::alert>
        <table>
            <thead>
                <tr>
                    <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                        <span class="font-bold">{{ __('tollerus::ui.activate') }}</span>
                    </th>
                    <th scope="col" class="text-left py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                        <span class="font-bold">{{ __('tollerus::ui.neography') }}</span>
                    </th>
                    <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                        <span class="font-bold">{{ __('tollerus::ui.edit') }}</span>
                    </th>
                    <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                        <span class="font-bold">{{ __('tollerus::ui.primary') }}</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($neographies as $neography)
                    <tr>
                        <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                            <x-tollerus::inputs.toggle
                                showLabel="false"
                                id="neography_{{ $neography->id }}"
                                model="neographiesForm.{{ $neography->id }}"
                                x-effect="
                                    if (!(neographiesForm[{{ $neography->id }}]) && neographiesForm.primary_neography == {{ $neography->id }}) {
                                        neographiesForm.primary_neography = null;
                                        $wire.set('neographiesForm.primary_neography', null);
                                    }
                                "
                                label="{{ __('tollerus::ui.activate_neography_in_language', [
                                    'neography' => $neography->name,
                                    'language' => $language->name
                                ]) }}"
                                @change="btn = 'save'; dirty=true;" />
                        </td>
                        <th scope="row" class="text-left px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                            <span x-bind:class="neographiesForm[{{ $neography->id }}] ? 'font-bold' : 'font-bold opacity-40'">{{ $neography->name }}</span>
                        </th>
                        <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                            <x-tollerus::button
                                type="secondary"
                                size="small"
                                title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                                href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography->id]) }}"
                                class="inline-flex align-middle justify-center items-center"
                            >
                                <x-tollerus::icons.edit />
                                <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}</span>
                            </x-tollerus::button>
                        </td>
                        <td class="text-center px-2 pb-1 pt-5 min-w-24 border-b-2 border-zinc-300 dark:border-zinc-700">
                            <label class="inline-block align-middle w-6 h-6 relative group">
                                <x-tollerus::icons.star
                                    x-bind:fill="neographiesForm.primary_neography == {{ $neography->id }} ? 'currentColor' : 'none'"
                                    class="rounded-lg text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:dark:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700 group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white"
                                />
                                <input
                                    type="radio"
                                    name="primary_neography"
                                    value="{{ $neography->id }}"
                                    wire:model="neographiesForm.primary_neography"
                                    x-bind:title="(neographiesForm[{{ $neography->id }}]) ? @js(__('tollerus::ui.set_primary_as_name', ['name' => $neography->name])) : @js(__('tollerus::ui.primary_must_be_active'))"
                                    x-bind:disabled="!(neographiesForm[{{ $neography->id }}])"
                                    class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                    @change="btn = 'save'; dirty=true;"
                                />
                                <span class="sr-only">{{ __('tollerus::ui.set_primary_as_name', ['name' => $neography->name]) }}</span>
                            </label>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex flex-col items-start gap-2">
            <x-tollerus::alert type="warning" x-cloak x-show="nativeSpellingsToDelete > 0">{{ __('tollerus::ui.associated_delete') }}</x-tollerus::alert>
            <div class="flex flex-row justify-start gap-2">
                <x-tollerus::inputs.button type="secondary" x-bind:disabled="!dirty" @click="$wire.refreshNeographiesForm(); dirty=false;">{{ __('tollerus::ui.reset') }}</x-tollerus::inputs.button>
                <x-tollerus::inputs.button
                    @click="if (nativeSpellingsToDelete > 0) {$dispatch('open-modal', {
                        message: nativeSpellingsMsg,
                        buttons: [
                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'modal-save' }
                        ]
                    })} else {btn = 'saving'; $wire.neographiesSave('',{});}"
                    x-bind:disabled="!dirty"
                    wire:loading.attr="disabled"
                    wire:target="neographiesSave"
                    @save-neographies-success.window="btn = 'saved'; dirty=false; if ($event.detail[0].afterSuccess) {$dispatch($event.detail[0].afterSuccess, $event.detail[0].payload);}"
                    @save-neographies-failure.window="btn = 'save';"
                    x-text="msgs[btn]" />
            </div>
        </div>
        @if ($errors->has('neographiesForm.*'))
            <div class="flex flex-row gap-4">
                @foreach (collect($errors->get('neographiesForm.*'))->flatten() as $message)
                    <x-tollerus::alert type="error">{{ $message }}</x-tollerus::alert>
                @endforeach
            </div>
        @endif
    @else
        <x-tollerus::missing-data>{{ __('tollerus::ui.no_neographies') }}</x-tollerus::missing-data>
    @endif
</x-tollerus::panel>
