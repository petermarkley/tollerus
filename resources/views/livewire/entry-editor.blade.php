<div
    x-data="{
        dirty: false,
        btn: 'saved',
        msgs: {
            save: @js(__('tollerus::ui.save')),
            saved: @js(__('tollerus::ui.saved')),
            saving: @js(__('tollerus::ui.saving')),
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
            delete_entry_confirmation: @js(__('tollerus::ui.delete_entry_confirmation')),
            delete_word_class_confirmation: @js(__('tollerus::ui.delete_word_class_confirmation')),
            delete_word_form_confirmation: @js(__('tollerus::ui.delete_word_form_confirmation')),
            delete_sense_confirmation: @js(__('tollerus::ui.delete_sense_confirmation')),
            delete_subsense_confirmation: @js(__('tollerus::ui.delete_subsense_confirmation')),
        },
        infoForm: $wire.entangle('infoForm'),
        wordClassGroups: $wire.entangle('wordClassGroups'),
        moveLexeme(lexemeElem, lexemeId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.infoForm.lexemes, lexemeId, dir);
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('lexeme_' + neighborId);
            $store.reorderFunctions.swapItems(lexemeElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapLexemes(lexemeId, neighborId);
            };
            lexemeElem.addEventListener('transitionend', onDone);
        },
        moveSense(lexemeId, senseElem, senseId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.infoForm.lexemes[lexemeId].senses, senseId, dir, 'num');
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('sense_' + neighborId);
            $store.reorderFunctions.swapItems(senseElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapSenses(lexemeId, senseId, neighborId);
            };
            senseElem.addEventListener('transitionend', onDone);
        },
        moveSubsense(lexemeId, senseId, subsenseElem, subsenseId, dir) {
            let neighborId = $store.reorderFunctions.getNeighborId(this.infoForm.lexemes[lexemeId].senses[senseId].subsenses, subsenseId, dir, 'num');
            if (neighborId === null) {
                return;
            }
            let neighborElem = document.getElementById('subsense_' + neighborId);
            $store.reorderFunctions.swapItems(subsenseElem, neighborElem);
            const onDone = (event) => {
                // Listener should be ephemeral
                event.target.removeEventListener('transitionend', onDone);
                // Livewire request
                $wire.swapSubsenses(lexemeId, senseId, subsenseId, neighborId);
            };
            subsenseElem.addEventListener('transitionend', onDone);
        },
    }"
    @modal-discard.window="$wire.refreshForm(); dirty=false;"
    @modal-save.window="$wire.save(tab, '', {});"
    @entry-delete.window="$store.entry.delete($event.detail.url);"
    @lexeme-delete.window="$wire.deleteLexeme($event.detail.lexemeId);"
    @form-delete.window="$wire.deleteForm($event.detail.formId);"
    @sense-delete.window="$wire.deleteSense($event.detail.senseId);"
    @subsense-delete.window="$wire.deleteSubsense($event.detail.subsenseId);"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0 flex flex-row gap-4 justify-between items-center">
            <span>{{ $pageTitle }}</span>
            <x-tollerus::inputs.button
                type="secondary"
                size="small"
                title="{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.entry')]) }}"
                @click="$dispatch('open-modal', {
                    message: msgs['delete_entry_confirmation'],
                    buttons: [
                        { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'entry-delete', payload: {url: '{{ route('tollerus.admin.languages.entries.destroy', ['language' => $language->id, 'entry' => $entry->id]) }}'} }
                    ]
                });"
            >
                <x-tollerus::icons.delete/>
                <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.entry')]) }}</span>
            </x-tollerus::inputs.button>
        </h1>
        <div class="flex flex-col gap-6">
            <x-tollerus::panel class="flex flex-col gap-4 items-start">
                <div class="flex flex-col w-full">
                    <p class="flex flex-row gap-4 items-center">
                        <span>{{ __('tollerus::ui.public_id') }}</span>
                        <span class="font-mono">{{ $entry->global_id }}</span>
                    </p>
                </div>
                <div class="flex flex-col gap-2 items-start">
                    <h3 class="font-bold text-lg">
                        <label for="primary_form" class="flex flex-row gap-4 items-center">
                            <x-tollerus::icons.word-class />
                            <span>{{ __('tollerus::ui.primary_form') }}</span>
                        </label>
                    </h3>
                    <div>
                        <x-tollerus::inputs.select
                            idExpression="'primary_form'"
                            label="{{ __('tollerus::ui.primary_form') }}"
                            showLabel="false"
                            model="infoForm.primaryForm"
                            @change="$wire.updatePrimaryForm($el.value);"
                        >
                            <option value="" class="cursor-pointer italic" x-bind:selected="infoForm.primaryForm===null || infoForm.primaryForm===''">{{ __('tollerus::ui.none') }}</option>
                            <template x-for="([lexemeId, lexeme], i) in $store.reorderFunctions.sortItems(infoForm.lexemes)">
                                <template x-if="Object.values(lexeme.forms).length > 0">
                                    <optgroup x-bind:label="lexeme.wordClassName">
                                        <template x-for="(form, formId) in lexeme.forms">
                                            <option x-bind:value="formId" class="cursor-pointer" x-text="form.transliterated" x-bind:selected="infoForm.primaryForm==formId"></option>
                                        </template>
                                    </optgroup>
                                </template>
                            </template>
                        </x-tollerus::inputs.select>
                    </div>
                    <template x-if="infoForm.primaryForm===null || infoForm.primaryForm.length==0">
                        <x-tollerus::alert type="warning">
                            <p>{{ __('tollerus::ui.missing_primary_form_alert') }}</p>
                        </x-tollerus::alert>
                    </template>
                </div>
                <div class="w-full flex flex-col gap-2">
                    <h3 class="font-bold text-lg">
                        <label for="etym" class="flex flex-row gap-4 items-center">
                            <x-tollerus::icons.academic-cap />
                            <span>{{ __('tollerus::ui.word_origin') }}</span>
                        </label>
                    </h3>
                    <x-tollerus::inputs.textarea id="etym" model="infoForm.etym" rows="2" @input="btn = 'save'; dirty=true;" />
                </div>
                <div class="flex flex-row justify-start gap-2">
                    <x-tollerus::inputs.button
                        @click="btn = 'saving'; $wire.infoSave();"
                        x-bind:disabled="!dirty"
                        wire:loading.attr="disabled"
                        wire:target="infoSave"
                        @save-info-success.window="btn = 'saved'; dirty=false;"
                        @save-info-failure.window="btn = 'save';"
                        x-text="msgs[btn]" />
                </div>
                <div class="w-full" x-data="{}" x-init="$store.phonemicKeyboard.mount($el)"></div>
            </x-tollerus::panel>
            <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                <template x-for="([lexemeId, lexeme], i) in $store.reorderFunctions.sortItems(infoForm.lexemes)">
                    <div
                        x-bind:id="'lexeme_' + lexemeId"
                        data-obj="lexeme"
                        x-data="{ wordClassGroup: wordClassGroups.find((g)=>g.id==lexeme.wordClassGroupId) }"
                        class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                        x-bind:style="'order: '+i"
                        @transitionend="$nextTick(() => {animating=false});"
                    >
                        <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_word_class_up') }}"
                                x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(infoForm.lexemes, lexemeId)"
                                @click="animating=true; moveLexeme($el.closest('[data-obj=&quot;lexeme&quot;]'), lexemeId, -1);"
                            >
                                <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_word_class_up') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_word_class_down') }}"
                                x-bind:disabled="animating || $store.reorderFunctions.isLastItem(infoForm.lexemes, lexemeId)"
                                @click="animating=true; moveLexeme($el.closest('[data-obj=&quot;lexeme&quot;]'), lexemeId, +1);"
                            >
                                <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_word_class_down') }}</span>
                            </x-tollerus::inputs.button>
                        </x-tollerus::panel>
                        <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                            <div class="flex flex-row gap-2 items-center justify-between">
                                <div class="flex flex-col md:flex-row gap-y-4 gap-x-8 items-start md:items-center flex-grow">
                                    <h2 class="font-bold text-xl flex flex-row gap-2 items-center">
                                        <x-tollerus::icons.lightbulb class="shrink-0"/>
                                        <span x-text="lexeme.wordClassName" x-bind:class="{ 'font-normal italic': lexeme.wordClassName.length==0 }"></span>
                                    </h2>
                                    <div class="flex flex-row gap-4 items-center">
                                        <span>{{ __('tollerus::ui.public_id') }}</span>
                                        <span x-text="lexeme.globalId" class="font-mono"></span>
                                    </div>
                                </div>
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.word_class')]) }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_word_class_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'lexeme-delete', payload: {lexemeId: lexemeId} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.word_class')]) }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                            <template x-if="lexeme.hasMissingForms">
                                <x-tollerus::alert type="warning">
                                    <p>{{ __('tollerus::ui.missing_forms_alert') }}</p>
                                    <x-tollerus::inputs.button
                                        type="primary"
                                        size="small"
                                        title="{{ __('tollerus::ui.add_missing_word_forms') }}"
                                        @click="$wire.createMissingForms(lexemeId);"
                                        wire:loading.attr="disabled"
                                        wire:target="createMissingForms"
                                        class="px-2"
                                    >
                                        <span>{{ __('tollerus::ui.add_missing_word_forms') }}</span>
                                    </x-tollerus::inputs.button>
                                </x-tollerus::alert>
                            </template>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <div class="flex flex-col md:flex-row gap-y-4 gap-x-8 items-start md:items-center">
                                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                        <x-tollerus::icons.fingerprint />
                                        <span>{{ __('tollerus::ui.word_forms') }}</span>
                                    </h3>
                                    <template x-if="lexeme.wasMatched && Object.values(wordClassGroup.features).length > 0">
                                        <x-tollerus::button
                                            type="secondary"
                                            size="small"
                                            title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}"
                                            x-bind:href="lexeme.inflectionEditUrl"
                                            class="flex flex-row gap-2 items-center px-2"
                                        >
                                            <x-tollerus::icons.edit />
                                            <span>{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}</span>
                                        </x-tollerus::button>
                                    </template>
                                </div>
                                <template x-for="([formId, form], i) in Object.entries(lexeme.forms)">
                                    <x-tollerus::panel class="flex flex-col gap-4 items-start w-full">
                                        <div class="flex flex-row gap-4 justify-between items-center w-full">
                                            <div class="flex flex-row gap-4 items-center">
                                                <span>{{ __('tollerus::ui.public_id') }}</span>
                                                <span x-text="form.globalId" class="font-mono"></span>
                                            </div>
                                            <x-tollerus::inputs.button
                                                type="inverse"
                                                size="small"
                                                class="align-middle"
                                                title="{{ __('tollerus::ui.delete_word_form') }}"
                                                @click="$dispatch('open-modal', {
                                                    message: msgs['delete_word_form_confirmation'],
                                                    buttons: [
                                                        { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'form-delete', payload: {formId: formId} }
                                                    ]
                                                });"
                                            >
                                                <x-tollerus::icons.delete/>
                                                <label class="sr-only">{{ __('tollerus::ui.delete_word_form') }}</label>
                                            </x-tollerus::inputs.button>
                                        </div>
                                        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center flex-grow">
                                            <div class="lg:w-80">
                                                <x-tollerus::inputs.text-saveable
                                                    idExpression="'form_' + formId + '_transliterated'"
                                                    model="form.transliterated"
                                                    fieldName="{{ config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated')) }}"
                                                    showLabel="true"
                                                    saveEvent="$wire.updateForm(lexemeId, formId, 'transliterated', document.getElementById(id).value, id);"
                                                >
                                                    <x-slot:before>
                                                        <template x-if="form.canAutoInflect">
                                                            <x-tollerus::inputs.button
                                                                type="secondary"
                                                                size="small"
                                                                class="align-middle"
                                                                title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                x-bind:disabled="lexeme.forms[form.srcForm].transliterated.length == 0"
                                                                @click="$wire.autoInflect(lexemeId, formId, form.matchingRowId, lexeme.forms[form.srcForm].transliterated, 'transliterated', null, id);"
                                                                wire:loading.attr="disabled"
                                                                wire:target="autoInflect"
                                                            >
                                                                <x-tollerus::icons.bolt fill="currentColor" />
                                                                <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                            </x-tollerus::inputs.button>
                                                        </template>
                                                    </x-slot:before>
                                                </x-tollerus::inputs.text-saveable>
                                            </div>
                                            <div class="lg:w-80">
                                                <x-tollerus::inputs.text-saveable
                                                    idExpression="'form_' + formId + '_phonemic'"
                                                    model="form.phonemic"
                                                    fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                    showLabel="true"
                                                    saveEvent="$wire.updateForm(lexemeId, formId, 'phonemic', document.getElementById(id).value, id);"
                                                >
                                                    <x-slot:before>
                                                        <template x-if="form.canAutoInflect">
                                                            <x-tollerus::inputs.button
                                                                type="secondary"
                                                                size="small"
                                                                class="align-middle"
                                                                title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                x-bind:disabled="lexeme.forms[form.srcForm].phonemic.length == 0"
                                                                @click="$wire.autoInflect(lexemeId, formId, form.matchingRowId, lexeme.forms[form.srcForm].phonemic, 'phonemic', null, id);"
                                                                wire:loading.attr="disabled"
                                                                wire:target="autoInflect"
                                                            >
                                                                <x-tollerus::icons.bolt fill="currentColor" />
                                                                <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                            </x-tollerus::inputs.button>
                                                        </template>
                                                    </x-slot:before>
                                                </x-tollerus::inputs.text-saveable>
                                            </div>
                                            <div class="flex flex-col items-start">
                                                <x-tollerus::inputs.checkbox
                                                    idExpression="'form_' + formId + '_irregular'"
                                                    model="form.irregular"
                                                    modelIsAlpine="true"
                                                    label="{{ __('tollerus::ui.irregular') }}"
                                                    @change="$wire.updateForm(lexemeId, formId, 'irregular', $el.checked, id);"
                                                />
                                            </div>
                                        </div>
                                        <table>
                                            <tbody>
                                                <template x-for="nativeSpelling in form.nativeSpellings">
                                                    <tr>
                                                        <th scope="row" x-text="nativeSpelling.neographyName" class="font-normal text-right pr-2 py-1"></th>
                                                        <td class="text-left pr-2 py-1 w-60">
                                                            <x-tollerus::inputs.text-saveable
                                                                idExpression="'native_spelling_' + nativeSpelling.neographyId"
                                                                model="nativeSpelling.spelling"
                                                                fieldName="{{ __('tollerus::ui.native_spelling') }}"
                                                                saveEvent="$wire.updateNativeSpelling(lexemeId, formId, nativeSpelling.neographyId, document.getElementById(id).value, id);"
                                                                x-bind:class="'tollerus_' + nativeSpelling.neographyMachineName"
                                                            >
                                                                <x-slot:before>
                                                                    <template x-if="form.canAutoInflect">
                                                                        <div x-data="{ srcSpelling: lexeme.forms[form.srcForm].nativeSpellings.find(sp => sp.neographyId==nativeSpelling.neographyId) }">
                                                                            <x-tollerus::inputs.button
                                                                                type="secondary"
                                                                                size="small"
                                                                                class="align-middle"
                                                                                title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                                x-bind:disabled="typeof srcSpelling.spelling !== 'string' || srcSpelling.spelling.length == 0"
                                                                                @click="$wire.autoInflect(lexemeId, formId, form.matchingRowId, srcSpelling.spelling, 'native', nativeSpelling.neographyId, id);"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="autoInflect"
                                                                            >
                                                                                <x-tollerus::icons.bolt fill="currentColor" />
                                                                                <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                            </x-tollerus::inputs.button>
                                                                        </div>
                                                                    </template>
                                                                </x-slot:before>
                                                            </x-tollerus::inputs.text-saveable>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                        <template x-if="Object.values(wordClassGroup.features).length > 0">
                                            <div class="flex flex-col gap-4 items-start w-full">
                                                <h4>{{ __('tollerus::ui.inflection_values') }}</h4>
                                                <div class="pl-12 flex flex-col gap-2 items-start w-full">
                                                    <ul class="flex flex-row flex-wrap gap-2">
                                                        <template x-for="[valueId, value] in Object.entries(form.inflectionValues)">
                                                            <li class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1">
                                                                <span x-text="value.featureName + ': ' + value.valueName"></span>
                                                                <x-tollerus::inputs.button
                                                                    type="inverse"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.remove_value') }}"
                                                                    @click="$wire.removeFormValue(formId, value.valueId);"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="removeFormValue"
                                                                >
                                                                    <x-tollerus::icons.x/>
                                                                    <label class="sr-only">{{ __('tollerus::ui.remove_value') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            </li>
                                                        </template>
                                                    </ul>
                                                    <div class="flex flex-col md:flex-row gap-4 justify-start items-start md:items-center">
                                                        <template x-if="form.matchingRowId===null">
                                                            <x-tollerus::inputs.dropdown class="relative w-full">
                                                                <x-slot:button>
                                                                    <x-tollerus::inputs.missing-data
                                                                        size="small"
                                                                        title="{{ __('tollerus::ui.match_to_inflection_row') }}"
                                                                        class="relative flex flex-row gap-2 justify-center items-center"
                                                                        @click="open=true"
                                                                    >
                                                                        <x-tollerus::icons.link/>
                                                                        <span class="sr-only lg:not-sr-only !whitespace-nowrap">{{ __('tollerus::ui.match_to_inflection_row') }}</span>
                                                                    </x-tollerus::inputs.missing-data>
                                                                </x-slot:button>
                                                                <template x-for="table in wordClassGroup.tables">
                                                                    <div class="flex flex-col items-start">
                                                                        <span x-text="table.label" class="italic opacity-50"></span>
                                                                        <template x-for="row in table.rows">
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                size="small"
                                                                                x-text="row.label"
                                                                                @click="open=false; $wire.matchFormToRow(lexemeId, formId, table.id, row.id);"
                                                                                class="ml-4"
                                                                            />
                                                                        </template>
                                                                    </div>
                                                                </template>
                                                            </x-tollerus::inputs.dropdown>
                                                        </template>
                                                        <x-tollerus::inputs.dropdown class="relative w-full">
                                                            <x-slot:button>
                                                                <x-tollerus::inputs.missing-data
                                                                    size="small"
                                                                    title="{{ __('tollerus::ui.add_value') }}"
                                                                    class="relative flex flex-row gap-2 justify-center items-center"
                                                                    @click="open=true"
                                                                >
                                                                    <x-tollerus::icons.plus/>
                                                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_value') }}</span>
                                                                </x-tollerus::inputs.missing-data>
                                                            </x-slot:button>
                                                            <template x-for="feature in wordClassGroup.features">
                                                                <div class="flex flex-col items-start">
                                                                    <span x-text="feature.name" class="italic opacity-50"></span>
                                                                    <template x-for="value in feature.values">
                                                                        <x-tollerus::inputs.button
                                                                            type="inverse"
                                                                            size="small"
                                                                            x-bind:class="{'ml-4': true, 'line-through': Object.values(form.inflectionValues).map((v)=>v.featureId).includes(feature.id)}"
                                                                            x-bind:disabled="Object.values(form.inflectionValues).map((v)=>v.featureId).includes(feature.id);"
                                                                            x-text="value.name"
                                                                            @click="open=false; $wire.addFormValue(lexemeId, formId, value.id);"
                                                                        />
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </x-tollerus::inputs.dropdown>
                                                    </div>
                                                </div>
                                                <template x-if="form.matchingRowId!==null">
                                                    <p class="flex flex-row gap-3 justify-start items-center text-zinc-500 dark:text-zinc-500">
                                                        <x-tollerus::icons.check class="border-2 border-zinc-500 rounded-full"/>
                                                        <span class="italic">{{ __('tollerus::ui.matched_inflection_row') }}:</span>
                                                        <span class="font-bold" x-text="form.matchingRowLabel"></span>
                                                    </p>
                                                </template>
                                                <template x-if="lexeme.wasMatched && form.matchingRowCount < 1">
                                                    <x-tollerus::alert type="warning">
                                                        <p>{{ __('tollerus::ui.no_row_matches_alert') }}</p>
                                                    </x-tollerus::alert>
                                                </template>
                                                <template x-if="lexeme.wasMatched && form.matchingRowCount > 1">
                                                    <x-tollerus::alert type="warning">
                                                        <p>{{ __('tollerus::ui.multiple_row_matches_alert') }}</p>
                                                    </x-tollerus::alert>
                                                </template>
                                                <template x-if="lexeme.wasMatched && form.matchingRowHasOthers">
                                                    <x-tollerus::alert type="warning">
                                                        <p>{{ __('tollerus::ui.multiple_form_matches_alert') }}</p>
                                                    </x-tollerus::alert>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="form.transliterated.length==0">
                                            <x-tollerus::alert type="warning">
                                                <p>{{ __('tollerus::ui.word_form_not_transliterated_alert', ['transliteration' => Config::get('tollerus.local_transliteration_word', __('tollerus::ui.transliteration'))]) }}</p>
                                            </x-tollerus::alert>
                                        </template>
                                        <template x-if="Object.values(wordClassGroup.features).length == 0 && infoForm.primaryForm !== null && infoForm.primaryForm != formId">
                                            <x-tollerus::alert type="warning">
                                                <p>{{ __('tollerus::ui.non_primary_form_alert') }}</p>
                                            </x-tollerus::alert>
                                        </template>
                                    </x-tollerus::panel>
                                </template>
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_word_form') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createForm(lexemeId);"
                                    wire:loading.attr="disabled"
                                    wire:target="createForm"
                                >
                                    <x-tollerus::icons.plus/>
                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_form') }}</span>
                                </x-tollerus::inputs.missing-data>
                            </x-tollerus::pane>
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.scales />
                                    <span>{{ __('tollerus::ui.definition') }}</span>
                                </h3>
                                <template x-if="Object.keys(lexeme.senses).length > 0">
                                    <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                        <template x-for="([senseId, sense], i) in $store.reorderFunctions.sortItems(lexeme.senses, 'num')">
                                            <div
                                                x-bind:id="'sense_' + senseId"
                                                data-obj="sense"
                                                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                x-bind:style="'order: '+i"
                                                @transitionend="$nextTick(() => {animating=false});"
                                            >
                                                <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_sense_up') }}"
                                                        x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(lexeme.senses, senseId, 'num')"
                                                        @click="animating=true; moveSense(lexemeId, $el.closest('[data-obj=&quot;sense&quot;]'), senseId, -1);"
                                                    >
                                                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_sense_up') }}</span>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_sense_down') }}"
                                                        x-bind:disabled="animating || $store.reorderFunctions.isLastItem(lexeme.senses, senseId, 'num')"
                                                        @click="animating=true; moveSense(lexemeId, $el.closest('[data-obj=&quot;sense&quot;]'), senseId, +1);"
                                                    >
                                                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_sense_down') }}</span>
                                                    </x-tollerus::inputs.button>
                                                </x-tollerus::panel>
                                                <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none flex-grow">
                                                    <div class="flex flex-row gap-4 justify-between items-start w-full">
                                                        <h4 class="font-bold text-lg">
                                                            <span x-text="sense.num+'.'"></span>
                                                        </h4>
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            size="small"
                                                            class="align-middle"
                                                            title="{{ __('tollerus::ui.delete_word_sense') }}"
                                                            @click="$dispatch('open-modal', {
                                                                message: msgs['delete_sense_confirmation'],
                                                                buttons: [
                                                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'sense-delete', payload: {senseId: senseId} }
                                                                ]
                                                            });"
                                                        >
                                                            <x-tollerus::icons.delete/>
                                                            <label class="sr-only">{{ __('tollerus::ui.delete_word_sense') }}</label>
                                                        </x-tollerus::inputs.button>
                                                    </div>
                                                    <div data-obj="textarea-div" class="flex flex-col gap-2 items-start w-full" x-data="{ dirty: false, btn: 'saved', id: 'sense_'+senseId+'_body' }">
                                                        <textarea
                                                            x-bind:id="id"
                                                            rows="2"
                                                            x-model="sense.body"
                                                            @input="btn = 'save'; dirty=true;"
                                                            class="border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600" >
                                                        </textarea>
                                                        <x-tollerus::inputs.button
                                                            @click="
                                                                btn = 'saving';
                                                                e = $el.closest('[data-obj=&quot;textarea-div&quot;]').querySelector('textarea');
                                                                $wire.updateSense(lexemeId, senseId, 'body', e.value, id);
                                                            "
                                                            x-bind:disabled="!dirty"
                                                            wire:loading.attr="disabled"
                                                            wire:target="updateSense"
                                                            @sense-update-success.window="btn = 'saved'; dirty=false;"
                                                            @sense-update-failure.window="btn = 'save';"
                                                            x-text="msgs[btn]" />
                                                    </div>
                                                    <x-tollerus::drawer open="false" rootClass="w-full" class="flex flex-col gap-4 w-full">
                                                        <x-slot:heading-button>
                                                            <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-zinc-500 dark:bg-zinc-400 group-has-hover:bg-zinc-400 group-has-hover:dark:bg-zinc-300 text-white dark:text-zinc-800">
                                                                <span>{{ __('tollerus::ui.subsenses') }}</span>
                                                                <template x-if="Object.keys(sense.subsenses).length > 0">
                                                                    <span x-text="Object.keys(sense.subsenses).length" class="block font-bold text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 rounded-full w-6 h-6 flex justify-center items-center text-center"></span>
                                                                </template>
                                                            </div>
                                                        </x-slot:heading-button>
                                                        <x-slot:heading>
                                                            <div class="flex-grow border-b-2 border-zinc-500 dark:border-zinc-400"></div>
                                                        </x-slot:heading>
                                                        <template x-if="Object.keys(sense.subsenses).length > 0">
                                                            <div class="flex flex-col gap-4 items-start pl-12 w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                                                <template x-for="([subsenseId, subsense], i) in $store.reorderFunctions.sortItems(sense.subsenses, 'num')">
                                                                    <div
                                                                        x-bind:id="'subsense_' + subsenseId"
                                                                        data-obj="subsense"
                                                                        class="flex flex-row gap-2 w-full items-stretch transition-[transform] duration-500 ease-out"
                                                                        x-bind:style="'order: '+i"
                                                                        @transitionend="$nextTick(() => {animating=false});"
                                                                    >
                                                                        <div class="flex flex-col justify-start shrink-0">
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                title="{{ __('tollerus::ui.move_subsense_up') }}"
                                                                                x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(sense.subsenses, subsenseId, 'num')"
                                                                                @click="animating=true; moveSubsense(lexemeId, senseId, $el.closest('[data-obj=&quot;subsense&quot;]'), subsenseId, -1);"
                                                                            >
                                                                                <x-tollerus::icons.chevron-up class="h-6 w-6" />
                                                                                <span class="sr-only">{{ __('tollerus::ui.move_subsense_up') }}</span>
                                                                            </x-tollerus::inputs.button>
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                title="{{ __('tollerus::ui.move_subsense_down') }}"
                                                                                x-bind:disabled="animating || $store.reorderFunctions.isLastItem(sense.subsenses, subsenseId, 'num')"
                                                                                @click="animating=true; moveSubsense(lexemeId, senseId, $el.closest('[data-obj=&quot;subsense&quot;]'), subsenseId, +1);"
                                                                            >
                                                                                <x-tollerus::icons.chevron-down class="h-6 w-6" />
                                                                                <span class="sr-only">{{ __('tollerus::ui.move_subsense_down') }}</span>
                                                                            </x-tollerus::inputs.button>
                                                                        </div>
                                                                        <div data-obj="textarea-div" class="flex flex-col gap-2 items-start flex-grow" x-data="{ dirty: false, btn: 'saved', id: 'subsense_'+subsenseId+'_body' }">
                                                                            <div class="flex flex-row justify-end w-full">
                                                                                <x-tollerus::inputs.button
                                                                                    type="inverse"
                                                                                    size="small"
                                                                                    class="align-middle"
                                                                                    title="{{ __('tollerus::ui.delete_subsense') }}"
                                                                                    @click="$dispatch('open-modal', {
                                                                                        message: msgs['delete_subsense_confirmation'],
                                                                                        buttons: [
                                                                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'subsense-delete', payload: {subsenseId: subsenseId} }
                                                                                        ]
                                                                                    });"
                                                                                >
                                                                                    <x-tollerus::icons.delete/>
                                                                                    <label class="sr-only">{{ __('tollerus::ui.delete_subsense') }}</label>
                                                                                </x-tollerus::inputs.button>
                                                                            </div>
                                                                            <textarea
                                                                                x-bind:id="id"
                                                                                rows="2"
                                                                                x-model="subsense.body"
                                                                                @input="btn = 'save'; dirty=true;"
                                                                                class="border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600" >
                                                                            </textarea>
                                                                            <x-tollerus::inputs.button
                                                                                size="small"
                                                                                @click="
                                                                                    btn = 'saving';
                                                                                    e = $el.closest('[data-obj=&quot;textarea-div&quot;]').querySelector('textarea');
                                                                                    $wire.updateSubsense(lexemeId, senseId, subsenseId, 'body', e.value, id);
                                                                                "
                                                                                x-bind:disabled="!dirty"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="updateSubsense"
                                                                                @subsense-update-success.window="btn = 'saved'; dirty=false;"
                                                                                @subsense-update-failure.window="btn = 'save';"
                                                                                x-text="msgs[btn]" />
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <div class="pl-12">
                                                            <x-tollerus::inputs.missing-data
                                                                size="small"
                                                                title="{{ __('tollerus::ui.add_subsense') }}"
                                                                class="relative flex flex-row gap-2 justify-center items-center w-full"
                                                                @click="$wire.createSubsense(lexemeId, senseId);"
                                                                wire:loading.attr="disabled"
                                                                wire:target="createSubsense"
                                                            >
                                                                <x-tollerus::icons.plus/>
                                                                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_subsense') }}</span>
                                                            </x-tollerus::inputs.missing-data>
                                                        </div>
                                                    </x-tollerus::drawer>
                                                </x-tollerus::panel>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_word_sense') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createSense(lexemeId);"
                                    wire:loading.attr="disabled"
                                    wire:target="createSense"
                                >
                                    <x-tollerus::icons.plus/>
                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_sense') }}</span>
                                </x-tollerus::inputs.missing-data>
                            </x-tollerus::pane>
                        </x-tollerus::panel>
                    </div>
                </template>
            </div>
            <div class="px-6 xl:px-0">
                <x-tollerus::inputs.dropdown class="relative w-full">
                    <x-slot:button>
                        <x-tollerus::inputs.missing-data
                            size="medium" floating="true"
                            title="{{ __('tollerus::ui.add_word_class') }}"
                            class="relative flex flex-row gap-2 justify-center items-center w-full"
                            @click="open=true"
                        >
                            <x-tollerus::icons.plus/>
                            <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_class') }}</span>
                        </x-tollerus::inputs.missing-data>
                    </x-slot:button>
                    <template x-for="wordClassGroup in wordClassGroups">
                        <div class="flex flex-col items-start">
                            <span class="italic opacity-50">{{ __('tollerus::ui.group_nameless') }}</span>
                            <template x-for="wordClass in wordClassGroup.classes">
                                <x-tollerus::inputs.button
                                    type="inverse"
                                    size="small"
                                    x-bind:class="{'ml-4': true, 'line-through': Object.values(infoForm.lexemes).map((l)=>l.wordClassId).includes(wordClass.id)}"
                                    x-bind:disabled="Object.values(infoForm.lexemes).map((l)=>l.wordClassId).includes(wordClass.id);"
                                    x-text="wordClass.name"
                                    @click="open=false; $wire.createLexeme(wordClass.id);"
                                />
                            </template>
                        </div>
                    </template>
                </x-tollerus::inputs.dropdown>
            </div>
        </div>
    </div>
    <x-tollerus::modal/>
    <x-tollerus::keyboards.native :nativeKeyboards="$nativeKeyboards"/>
    <x-tollerus::keyboards.phonemic :phonemicKeyboard="$ipaKeyboard"/>
</div>
<x-tollerus::reorder-script/>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('entry', {
        delete(url) {
            fetch(url, {
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
            }).then(response => {
                if (response.ok) {
                    window.location.href = '{{ route('tollerus.admin.languages.edit.tab', ['language' => $language->id, 'tab' => 'entries']) }}';
                } else {
                    console.error('Delete failed:', response.status);
                }
            }).catch(error => console.error('Network error:', error));
        },
    });
});
</script>
@endpush
@endonce
