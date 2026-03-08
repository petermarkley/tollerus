<x-tollerus::panel id="tabpanel-grammar" role="tabpanel" x-cloak x-show="tab=='grammar'" class="flex flex-col gap-8 items-start">
    @if(count($grammarForm) == 0)
        <div class="flex flex-col gap-4 items-start w-full" x-data="{ btn: 'load', preset: '' }">
            <x-tollerus::alert>
                <p>{{ __('tollerus::ui.preset_notice') }}</p>
            </x-tollerus::alert>
            <div class="flex flex-col gap-4 items-start">
                <x-tollerus::inputs.select idExpression="'preset'" :options="$presetSelectOpts" label="{{ __('tollerus::ui.preset') }}" model="preset"/>
                <template x-if="preset.length > 0">
                    <div class="flex flex-col items-start gap-4">
                        <h3 class="font-bold text-lg" x-text="$wire.presetData[preset]['previewHeading']"></h3>
                        <x-tollerus::pane>
                            <div class="flex flex-col sm:flex-row gap-y-4 gap-x-12">
                                <div class="flex flex-col gap-1 justify-start items-start">
                                    <h4 class="font-bold text-base">{{ __('tollerus::ui.word_classes') }}</h4>
                                    <ul>
                                        <template x-for="group in $wire.presetData[preset]['groups']">
                                            <li class="flex flex-row gap-1 items-center">
                                                <span x-text="group.name"></span>
                                                <template x-if="group.featureNum > 0">
                                                    <span x-text="group.featureNum" class="block font-bold text-white dark:text-zinc-900 bg-zinc-700 dark:bg-zinc-300 rounded-full w-4 h-4 flex justify-center items-center text-center text-sm"></span>
                                                </template>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div class="flex flex-col gap-1 justify-start items-start">
                                    <h4 class="font-bold text-base">{{ __('tollerus::ui.features') }}</h4>
                                    <ul>
                                        <template x-for="feature in $wire.presetData[preset]['features']">
                                            <li><span x-text="feature"></span></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </x-tollerus::pane>
                        <p x-text="$wire.presetData[preset]['description']"></p>
                        <x-tollerus::inputs.button
                            x-text="msgs[btn]"
                            @click="btn = 'loading'; $wire.loadGrammarPreset(preset);"
                            @preset-button-failure.window="btn = 'load';"
                            @preset-button-success.window="btn = 'load'; preset = '';"
                            x-bind:disabled="preset.length == 0"
                            wire:loading.attr="disabled"
                            wire:target="loadGrammarPreset"
                        />
                    </div>
                </template>
            </div>
            @error('preset')
                <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
            @enderror
        </div>
    @endif
    @foreach ($grammarForm as $groupId => $group)
        <div class="w-full" id="group_{{ $groupId }}">
            <x-tollerus::drawer open="false" rootClass="w-full" class="flex flex-col gap-4 w-full">
                <x-slot:heading-button>
                    <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-zinc-500 dark:bg-zinc-400 group-has-hover:bg-zinc-400 group-has-hover:dark:bg-zinc-300 text-white dark:text-zinc-800">
                        <x-tollerus::icons.folder x-show="!drawerOpen" />
                        <x-tollerus::icons.folder-open x-show="drawerOpen" x-cloak />
                        @if ($group['primaryClass'] === null)
                            <span class="font-normal italic">{{ __('tollerus::ui.group_nameless') }}</span>
                        @else
                            <span>{{ $group['classes'][$group['primaryClass']]['name'] }}</span>
                        @endif
                        @if (count($group['features']) > 0)
                            <span class="block font-bold text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 rounded-full w-6 h-6 flex justify-center items-center text-center">{{ count($group['features']) }}</span>
                        @endif
                    </div>
                </x-slot:heading-button>
                <x-slot:heading>
                    <div class="flex-grow border-b-2 border-zinc-500 dark:border-zinc-400"></div>
                    <button
                        title="{{ __('tollerus::ui.delete_word_class_group') }}"
                        @click="$dispatch('open-modal', {
                            message: msgs['delete_word_class_group_confirmation'],
                            buttons: [
                                { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-group-delete', payload: {groupId: '{{ $groupId }}'} }
                            ]
                        });"
                        class="relative flex p-1 justify-center items-center rounded-t-lg rounded-br bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 cursor-pointer"
                    >
                        <x-tollerus::icons.delete/>
                        <span class="sr-only">{{ __('tollerus::ui.delete_word_class_group') }}</span>
                    </button>
                </x-slot:heading>
                <x-tollerus::pane class="flex flex-col gap-4 items-start">
                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                        <x-tollerus::icons.word-class />
                        <span>{{ __('tollerus::ui.word_classes') }}</span>
                    </h3>
                    @if (count($group['classes']) > 0)
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col" class="text-left py-1 pr-2 w-60 border-b-2 border-zinc-400 dark:border-zinc-600">
                                        <span class="font-bold">{{ __('tollerus::ui.name') }}</span>
                                    </th>
                                    <th scope="col" class="text-left py-1 px-2 w-60 border-b-2 border-zinc-400 dark:border-zinc-600">
                                        <span class="font-bold">{{ __('tollerus::ui.abbreviation') }}</span>
                                    </th>
                                    <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                                        <span class="font-bold">{{ __('tollerus::ui.primary') }}</span>
                                    </th>
                                    <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                                        <span class="font-bold">{{ __('tollerus::ui.delete') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['classes'] as $wordClassId => $wordClass)
                                    <tr id="class_{{ $wordClassId }}">
                                        <td class="text-left pr-2 py-1 w-60">
                                            <x-tollerus::inputs.text-saveable
                                                idExpression="'class_{{ $wordClassId }}_name'"
                                                model="grammarForm.{{ $groupId }}.classes.{{ $wordClassId }}.name"
                                                fieldName="{{ __('tollerus::ui.name') }}"
                                                saveEvent="$wire.updateClass({{ $groupId }}, {{ $wordClassId }}, 'name', prop, id);" />
                                        </td>
                                        <td class="text-left px-2 py-1 w-60">
                                            <x-tollerus::inputs.text-saveable
                                                idExpression="'class_{{ $wordClassId }}_name_brief'"
                                                model="grammarForm.{{ $groupId }}.classes.{{ $wordClassId }}.nameBrief"
                                                fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                saveEvent="$wire.updateClass({{ $groupId }}, {{ $wordClassId }}, 'name_brief', prop, id);" />
                                        </td>
                                        <td class="text-center px-2 py-1 min-w-24">
                                            <label class="relative inline-block align-middle w-6 h-6 group">
                                                <x-tollerus::icons.star
                                                    x-bind:fill="'{{ ($group['primaryClass'] == $wordClassId ? 'currentColor' : 'none') }}'"
                                                    class="rounded-lg text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:dark:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700 group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white"
                                                />
                                                <input
                                                    type="radio"
                                                    name="'group_{{ $groupId }}_primary_class'"
                                                    value="{{ $wordClassId }}"
                                                    title="{{ __('tollerus::ui.set_this_as_primary') }}"
                                                    wire:model="grammarForm.{{ $groupId }}.primaryClass"
                                                    class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                                    @change.once="$wire.updateGroupPrimaryClass({{ $groupId }});"
                                                />
                                                <span class="sr-only">{{ __('tollerus::ui.set_this_as_primary') }}</span>
                                            </label>
                                        </td>
                                        <td class="text-center px-2 py-1 min-w-24">
                                            <x-tollerus::inputs.button
                                                type="inverse"
                                                size="small"
                                                class="align-middle"
                                                title="{{ __('tollerus::ui.delete_word_class') }}"
                                                @click="$dispatch('open-modal', {
                                                    message: msgs['delete_word_class_confirmation'],
                                                    buttons: [
                                                        { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-class-delete', payload: {wordClassId: '{{ $wordClassId }}'} }
                                                    ]
                                                });"
                                            >
                                                <x-tollerus::icons.delete/>
                                                <label class="sr-only">{{ __('tollerus::ui.delete_word_class') }}</label>
                                            </x-tollerus::inputs.button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                    <x-tollerus::inputs.missing-data
                        size="small"
                        title="{{ __('tollerus::ui.add_word_class') }}"
                        class="relative flex flex-row gap-2 justify-center items-center w-full"
                        @click="$wire.createWordClass('{{ $groupId }}', false);"
                        wire:loading.attr="disabled"
                        wire:target="createWordClass"
                    >
                        <x-tollerus::icons.plus/>
                        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_class') }}</span>
                    </x-tollerus::inputs.missing-data>
                </x-tollerus::pane>
                <x-tollerus::pane class="flex flex-col gap-4 items-start">
                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                        <x-tollerus::icons.feature />
                        <span>{{ __('tollerus::ui.features') }}</span>
                    </h3>
                    @if (count($group['features']) > 0)
                        <div class="flex flex-col gap-4 items-start">
                            @foreach ($group['features'] as $featureId => $feature)
                                <x-tollerus::panel
                                    id="feature_{{ $featureId }}"
                                    class="flex flex-col gap-1 items-start"
                                >
                                    <div class="flex flex-row gap-4 items-center">
                                        <div class="w-80">
                                            <x-tollerus::inputs.text-saveable
                                                idExpression="'feature_{{ $featureId }}_name'"
                                                model="grammarForm.{{ $groupId }}.features.{{ $featureId }}.name"
                                                fieldName="{{ __('tollerus::ui.name') }}"
                                                showLabel="true"
                                                saveEvent="$wire.updateFeature({{ $groupId }}, {{ $featureId }}, 'name', prop, id);" />
                                        </div>
                                        <div class="w-80">
                                            <x-tollerus::inputs.text-saveable
                                                idExpression="'feature_{{ $featureId }}_name_brief'"
                                                model="grammarForm.{{ $groupId }}.features.{{ $featureId }}.nameBrief"
                                                fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                showLabel="true"
                                                saveEvent="$wire.updateFeature({{ $groupId }}, {{ $featureId }}, 'name_brief', prop, id);" />
                                        </div>
                                        <div class="min-w-24 text-right">
                                            <x-tollerus::inputs.button
                                                type="inverse"
                                                size="small"
                                                class="align-middle"
                                                title="{{ __('tollerus::ui.delete_feature') }}"
                                                @click="$dispatch('open-modal', {
                                                    message: msgs['delete_feature_confirmation'],
                                                    buttons: [
                                                        { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-feature-delete', payload: {featureId: {{ $featureId }}} }
                                                    ]
                                                });"
                                            >
                                                <x-tollerus::icons.delete/>
                                                <label class="sr-only">{{ __('tollerus::ui.delete_feature') }}</label>
                                            </x-tollerus::inputs.button>
                                        </div>
                                    </div>
                                    <div class="pl-12">
                                        @if (count($feature['featureValues']) > 0)
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="text-left py-1 pr-2 w-60 border-b-2 border-zinc-400 dark:border-zinc-600">
                                                            <span class="font-bold">{{ __('tollerus::ui.feature_value') }}</span>
                                                        </th>
                                                        <th scope="col" class="text-left py-1 px-2 w-60 border-b-2 border-zinc-400 dark:border-zinc-600">
                                                            <span class="font-bold">{{ __('tollerus::ui.abbreviation') }}</span>
                                                        </th>
                                                        <th scope="col" class="text-center py-1 px-2 min-w-24 border-b-2 border-zinc-400 dark:border-zinc-600">
                                                            <span class="font-bold">{{ __('tollerus::ui.delete') }}</span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($feature['featureValues'] as $featureValueId => $featureValue)
                                                        <tr id="value_{{ $featureValueId }}">
                                                            <td class="text-left pr-2 py-1 w-60">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'value_{{ $featureValueId }}_name'"
                                                                    model="grammarForm.{{ $groupId }}.features.{{ $featureId }}.featureValues.{{ $featureValueId }}.name"
                                                                    fieldName="{{ __('tollerus::ui.name') }}"
                                                                    saveEvent="$wire.updateFeatureValue({{ $groupId }}, {{ $featureId }}, {{ $featureValueId }}, 'name', prop, id);" />
                                                            </td>
                                                            <td class="text-left px-2 py-1 w-60">
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'value_{{ $featureValueId }}_name_brief'"
                                                                    model="grammarForm.{{ $groupId }}.features.{{ $featureId }}.featureValues.{{ $featureValueId }}.nameBrief"
                                                                    fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                                    saveEvent="$wire.updateFeatureValue({{ $groupId }}, {{ $featureId }}, {{ $featureValueId }}, 'name_brief', prop, id);" />
                                                            </td>
                                                            <td class="text-center px-2 py-1 min-w-24">
                                                                <x-tollerus::inputs.button
                                                                    type="inverse"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.delete_feature_value') }}"
                                                                    @click="$dispatch('open-modal', {
                                                                        message: msgs['delete_feature_value_confirmation'],
                                                                        buttons: [
                                                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-value-delete', payload: {featureValueId: {{ $featureValueId }}} }
                                                                        ]
                                                                    });"
                                                                >
                                                                    <x-tollerus::icons.delete/>
                                                                    <label class="sr-only">{{ __('tollerus::ui.delete_feature_value') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                        <x-tollerus::inputs.missing-data
                                            size="small"
                                            title="{{ __('tollerus::ui.add_feature_value') }}"
                                            class="relative flex flex-row gap-2 justify-center items-center w-full"
                                            @click="$wire.createFeatureValue({{ $groupId }}, {{ $featureId }});"
                                            wire:loading.attr="disabled"
                                            wire:target="createFeatureValue"
                                        >
                                            <x-tollerus::icons.plus/>
                                            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_feature_value') }}</span>
                                        </x-tollerus::inputs.missing-data>
                                    </div>
                                </x-tollerus::panel>
                            @endforeach
                        </div>
                    @endif
                    <x-tollerus::inputs.missing-data
                        size="small"
                        title="{{ __('tollerus::ui.add_feature') }}"
                        class="relative flex flex-row gap-2 justify-center items-center w-full"
                        @click="$wire.createFeature({{ $groupId }});"
                        wire:loading.attr="disabled"
                        wire:target="createFeature"
                    >
                        <x-tollerus::icons.plus/>
                        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_feature') }}</span>
                    </x-tollerus::inputs.missing-data>
                </x-tollerus::pane>
                @if (count($group['features']) > 0)
                    <x-tollerus::pane class="flex flex-col gap-4 items-start">
                        <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                            <x-tollerus::icons.table />
                            <span>{{ __('tollerus::ui.inflection_tables') }}</span>
                        </h3>
                        @if (count($group['tables']) > 0)
                            <div class="w-full flex flex-row flex-wrap gap-4 justify-center items-center">
                                @foreach ($group['tables'] as $table)
                                    <div class="p-2 rounded-lg flex flex-row flex-wrap gap-2 justify-center items-center bg-white dark:bg-zinc-800 shadow-lg">
                                        @foreach ($table['columns'] as $column)
                                            <div class="rounded-lg overflow-hidden border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                                <table>
                                                    <thead>
                                                        <tr><th scope="col" colspan="2" class="text-center px-4 py-2 font-normal">{{ $column['label'] }}</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($column['rows'] as $row)
                                                            <tr>
                                                                <th scope="row" class="text-right p-1 font-normal border-t border-r border-zinc-300 dark:border-zinc-600">
                                                                    <abbr
                                                                        title="{{ (empty($row['labelLong']) ? $row['label'] : $row['labelLong']) }}"
                                                                        class="no-underline"
                                                                    >{{ (empty($row['labelBrief']) ? mb_substr($row['label'],0,3) : $row['labelBrief']) }}</abbr>
                                                                </th>
                                                                <td class="text-center px-4 py-1 border-t border-zinc-300 dark:border-zinc-600">&hellip;</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                                <x-tollerus::button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}"
                                    href="{{ $group['inflectionsUrl'] }}"
                                    class="flex flex-row gap-2 items-center"
                                >
                                    <x-tollerus::icons.edit class="m-2"/>
                                    <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}</span>
                                </x-tollerus::button>
                            </div>
                        @endif
                        @if (count($group['tables']) == 0)
                            <div class="flex flex-row justify-center items-center w-full">
                                <x-tollerus::missing-data href="{{ $group['inflectionsUrl'] }}">{{ __('tollerus::ui.no_inflection_tables') }}</x-tollerus::missing-data>
                            </div>
                        @endif
                    </x-tollerus::pane>
                @endif
            </x-tollerus::drawer>
        </div>
    @endforeach
    <div class="flex flex-col gap-6 items-center w-full">
        <x-tollerus::inputs.missing-data
            title="{{ __('tollerus::ui.add_word_class_group') }}"
            class="relative flex flex-row gap-2 justify-center items-center w-full"
            @click="$wire.createGroup();"
            wire:loading.attr="disabled"
            wire:target="createGroup"
        >
            <x-tollerus::icons.plus/>
            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_class_group') }}</span>
        </x-tollerus::inputs.missing-data>
    </div>
</x-tollerus::panel>
