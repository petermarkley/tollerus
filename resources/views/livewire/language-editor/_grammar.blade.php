<x-tollerus::panel id="tabpanel-grammar" role="tabpanel" x-cloak x-show="tab=='grammar'" class="flex flex-col gap-8 items-start">
    <div x-cloak wire:show="grammarForm.length == 0" class="flex flex-col gap-4 items-start w-full" x-data="{ btn: 'load', preset: '' }">
        <x-tollerus::alert>
            <p>{{ __('tollerus::ui.preset_notice') }}</p>
        </x-tollerus::alert>
        <div class="flex flex-col gap-4 items-start">
            <x-tollerus::inputs.select id="preset" :options="$presetSelectOpts" label="{{ __('tollerus::ui.preset') }}" model="preset"/>
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
    <template x-for="(group, groupId) in grammarForm">
        <x-tollerus::drawer open="false" rootClass="w-full" class="flex flex-col gap-4 w-full">
            <x-slot:heading>
                <h2 class="font-bold text-xl flex flex-row items-end w-full">
                    <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-zinc-500 dark:bg-zinc-400 text-white dark:text-zinc-800">
                        <x-tollerus::icons.folder x-show="!drawerOpen" />
                        <x-tollerus::icons.folder-open x-show="drawerOpen" />
                        <span x-text="group.primaryClass === null ? msgs['group_nameless'] : group.classes[group.primaryClass].name" x-bind:class="{ 'font-normal italic': group.primaryClass === null }"></span>
                    </div>
                    <div class="flex-grow border-b-2 border-zinc-500 dark:border-zinc-400"></div>
                    <button
                        title="{{ __('tollerus::ui.delete_word_class_group') }}"
                        @click="$dispatch('open-modal', {
                            message: msgs['delete_word_class_group_confirmation'],
                            buttons: [
                                { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-group-delete', payload: {groupId: groupId} }
                            ]
                        });"
                        class="flex p-1 justify-center items-center rounded-t-lg rounded-br bg-zinc-600 dark:bg-zinc-400 hover:bg-zinc-500 hover:dark:bg-zinc-300 text-white dark:text-zinc-950 cursor-pointer"
                    >
                        <x-tollerus::icons.delete/>
                        <span class="sr-only">{{ __('tollerus::ui.delete_word_class_group') }}</span>
                    </button>
                </h2>
            </x-slot:heading>
            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                    <x-tollerus::icons.word-class />
                    <span>{{ __('tollerus::ui.word_classes') }}</span>
                </h3>
                <template x-if="Object.keys(group.classes).length > 0">
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
                            <template x-for="(wordClass, wordClassId) in group.classes">
                                <tr>
                                    <td class="text-left pr-2 py-1 w-60">
                                        <x-tollerus::inputs.text-saveable
                                            idExpression="'class_' + wordClassId + '_name'"
                                            model="wordClass.name"
                                            fieldName="{{ __('tollerus::ui.name') }}"
                                            saveEvent="$wire.updateClass(groupId, wordClassId, 'name', document.getElementById(id).value);" />
                                    </td>
                                    <td class="text-left px-2 py-1 w-60">
                                        <x-tollerus::inputs.text-saveable
                                            idExpression="'class_' + wordClassId + '_name_brief'"
                                            model="wordClass.nameBrief"
                                            fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                            saveEvent="$wire.updateClass(groupId, wordClassId, 'name_brief', document.getElementById(id).value);" />
                                    </td>
                                    <td class="text-center px-2 py-1 min-w-24">
                                        <label class="inline-block align-middle w-6 h-6 relative group">
                                            <x-tollerus::icons.star
                                                x-bind:fill="group.primaryClass == wordClassId ? 'currentColor' : 'none'"
                                                class="rounded-lg text-zinc-600 group-has-hover:text-zinc-500 dark:text-zinc-500 group-has-hover:dark:text-zinc-400 group-has-checked:text-cyan-800 group-has-checked:group-has-hover:text-cyan-700 group-has-checked:dark:text-cyan-300 group-has-checked:group-has-hover:dark:text-cyan-200 group-has-checked:dark:saturate-50 group-has-disabled:text-zinc-300 group-has-disabled:dark:text-zinc-700 group-has-checked:group-has-hover:group-has-disabled:text-zinc-300 group-has-checked:group-has-hover:group-has-disabled:dark:text-zinc-700 group-has-focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white"
                                            />
                                            <input
                                                type="radio"
                                                x-bind:name="'group_' + groupId + '_primary_class'"
                                                x-bind:value="wordClassId"
                                                title="{{ __('tollerus::ui.set_this_as_primary') }}"
                                                x-model="group.primaryClass"
                                                class="absolute w-full h-full inset-0 opacity-0 z-10 cursor-pointer disabled:cursor-not-allowed"
                                                @change.once="$wire.updateGroupPrimaryClass(groupId);"
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
                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-class-delete', payload: {wordClassId: wordClassId} }
                                                ]
                                            });"
                                        >
                                            <x-tollerus::icons.delete/>
                                            <label class="sr-only">{{ __('tollerus::ui.delete_word_class') }}</label>
                                        </x-tollerus::inputs.button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
                <x-tollerus::inputs.missing-data
                    size="small"
                    title="{{ __('tollerus::ui.add_word_class') }}"
                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                    @click="$wire.createWordClass(groupId, false);"
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
                <template x-if="Object.keys(group.features).length > 0">
                    <div class="flex flex-col gap-4 items-start">
                        <template x-for="(feature, featureId) in group.features">
                            <x-tollerus::panel class="flex flex-col gap-1 items-start">
                                <div class="flex flex-row gap-4 items-center">
                                    <div class="w-80">
                                        <x-tollerus::inputs.text-saveable
                                            idExpression="'feature_' + featureId + '_name'"
                                            model="feature.name"
                                            fieldName="{{ __('tollerus::ui.name') }}"
                                            showLabel="true"
                                            saveEvent="$wire.updateFeature(groupId, featureId, 'name', document.getElementById(id).value);" />
                                    </div>
                                    <div class="w-80">
                                        <x-tollerus::inputs.text-saveable
                                            idExpression="'feature_' + featureId + '_name_brief'"
                                            model="feature.nameBrief"
                                            fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                            showLabel="true"
                                            saveEvent="$wire.updateFeature(groupId, featureId, 'name_brief', document.getElementById(id).value);" />
                                    </div>
                                    <div class="min-w-24">
                                        <x-tollerus::inputs.button
                                            type="inverse"
                                            size="small"
                                            class="align-middle"
                                            title="{{ __('tollerus::ui.delete_feature') }}"
                                            @click="$dispatch('open-modal', {
                                                message: msgs['delete_feature_confirmation'],
                                                buttons: [
                                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-feature-delete', payload: {featureId: featureId} }
                                                ]
                                            });"
                                        >
                                            <x-tollerus::icons.delete/>
                                            <label class="sr-only">{{ __('tollerus::ui.delete_feature') }}</label>
                                        </x-tollerus::inputs.button>
                                    </div>
                                </div>
                                <div class="pl-12">
                                    <template x-if="Object.keys(feature.featureValues).length > 0">
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
                                                <template x-for="(featureValue, featureValueId) in feature.featureValues">
                                                    <tr>
                                                        <td class="text-left pr-2 py-1 w-60">
                                                            <x-tollerus::inputs.text-saveable
                                                                idExpression="'value_' + featureValueId + '_name'"
                                                                model="featureValue.name"
                                                                fieldName="{{ __('tollerus::ui.name') }}"
                                                                saveEvent="$wire.updateFeatureValue(groupId, featureId, featureValueId, 'name', document.getElementById(id).value);" />
                                                        </td>
                                                        <td class="text-left px-2 py-1 w-60">
                                                            <x-tollerus::inputs.text-saveable
                                                                idExpression="'value_' + featureValueId + '_name_brief'"
                                                                model="featureValue.nameBrief"
                                                                fieldName="{{ __('tollerus::ui.abbreviation') }}"
                                                                saveEvent="$wire.updateFeatureValue(groupId, featureId, featureValueId, 'name_brief', document.getElementById(id).value);" />
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
                                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'grammar-value-delete', payload: {featureValueId: featureValueId} }
                                                                    ]
                                                                });"
                                                            >
                                                                <x-tollerus::icons.delete/>
                                                                <label class="sr-only">{{ __('tollerus::ui.delete_feature_value') }}</label>
                                                            </x-tollerus::inputs.button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </template>
                                    <x-tollerus::inputs.missing-data
                                        size="small"
                                        title="{{ __('tollerus::ui.add_feature_value') }}"
                                        class="relative flex flex-row gap-2 justify-center items-center w-full"
                                        @click="$wire.createFeatureValue(groupId, featureId);"
                                        wire:loading.attr="disabled"
                                        wire:target="createFeatureValue"
                                    >
                                        <x-tollerus::icons.plus/>
                                        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_feature_value') }}</span>
                                    </x-tollerus::inputs.missing-data>
                                </div>
                            </x-tollerus::panel>
                        </template>
                    </div>
                </template>
                <x-tollerus::inputs.missing-data
                    size="small"
                    title="{{ __('tollerus::ui.add_feature') }}"
                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                    @click="$wire.createFeature(groupId);"
                    wire:loading.attr="disabled"
                    wire:target="createFeature"
                >
                    <x-tollerus::icons.plus/>
                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_feature') }}</span>
                </x-tollerus::inputs.missing-data>
            </x-tollerus::pane>
            <template x-if="Object.keys(group.features).length > 0">
                <x-tollerus::pane class="flex flex-col gap-4 items-start">
                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                        <x-tollerus::icons.table />
                        <span>{{ __('tollerus::ui.inflection_tables') }}</span>
                    </h3>
                    <template x-if="Object.keys(group.tables).length > 0">
                        <div class="flex flex-row flex-wrap justify-center items-center gap-4 w-full">
                            <template x-for="(table, tableId) in group.tables">
                                <div class="rounded-lg shadow overflow-hidden border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-sm">
                                    <table>
                                        <thead>
                                            <tr><th scope="col" colspan="2" x-text="table.label" class="text-center px-4 py-2 font-normal"></th></tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(row, rowId) in table.rows">
                                                <tr>
                                                    <th scope="row" class="text-right p-1 font-normal border-t border-r border-zinc-300 dark:border-zinc-600">
                                                        <abbr x-bind:title="row.label" x-text="row.labelBrief || row.label.slice(0,3)" class="no-underline"></abbr>
                                                    </th>
                                                    <td class="text-center px-4 py-1 border-t border-zinc-300 dark:border-zinc-600">&hellip;</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            <x-tollerus::button
                                type="secondary"
                                size="small"
                                title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}"
                                href="{{ route('tollerus.admin.languages.inflection-tables', ['language' => $language]) }}"
                                class="flex flex-row gap-2 items-center"
                            >
                                <x-tollerus::icons.edit class="m-2"/>
                                <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}</span>
                            </x-tollerus::button>
                        </div>
                    </template>
                    <template x-if="Object.keys(group.tables).length == 0">
                        <div class="flex flex-row justify-center items-center w-full">
                            <x-tollerus::missing-data>{{ __('tollerus::ui.no_inflection_tables') }}</x-tollerus::missing-data>
                        </div>
                    </template>
                </x-tollerus::pane>
            </template>
        </x-tollerus::drawer>
    </template>
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
