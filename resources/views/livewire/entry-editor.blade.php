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
    }"
    @modal-discard.window="$wire.refreshForm(); dirty=false;"
    @modal-save.window="$wire.save(tab, '', {});"
    @entry-delete.window="$store.entry.delete($event.detail.url);"
    @lexeme-delete.window="$wire.deleteLexeme($event.detail.lexemeId);"
    @form-delete.window="$wire.deleteForm($event.detail.formId);"
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
            </x-tollerus::panel>
            <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                <template x-for="([lexemeId, lexeme], i) in $store.reorderFunctions.sortItems(infoForm.lexemes)">
                    <div
                        x-bind:id="'lexeme_' + lexemeId"
                        data-obj="lexeme"
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
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                    <x-tollerus::icons.fingerprint />
                                    <span>{{ __('tollerus::ui.word_forms') }}</span>
                                </h3>
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
                                                />
                                            </div>
                                            <div class="lg:w-80">
                                                <x-tollerus::inputs.text-saveable
                                                    idExpression="'form_' + formId + '_phonemic'"
                                                    model="form.phonemic"
                                                    fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                    showLabel="true"
                                                    saveEvent="$wire.updateForm(lexemeId, formId, 'phonemic', document.getElementById(id).value, id);"
                                                />
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
                                                                x-bind:class="'tollerus_' + nativeSpelling.neographyMachineName" />
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
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
                                                <template x-for="feature in wordClassGroups.find((g)=>g.id==lexeme.wordClassGroupId).features">
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
