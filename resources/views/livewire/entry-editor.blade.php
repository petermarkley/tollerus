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
        moveLexeme(lexemeElem, lexemeId, neighborId) {
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
        moveSense(lexemeId, senseElem, senseId, neighborId) {
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
        moveSubsense(lexemeId, senseId, subsenseElem, subsenseId, neighborId) {
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
        deleteItem(id) {
            let e = document.getElementById(id);
            if (e) {
                e.remove();
            }
        },
    }"
    @modal-discard.window="$wire.refreshForm(); dirty=false;"
    @modal-save.window="$wire.save(tab, '', {});"
    @entry-delete.window="$store.entry.delete($event.detail.url);"
    @lexeme-delete.window="deleteItem('lexeme_'+$event.detail.lexemeId); $wire.deleteLexeme($event.detail.lexemeId);"
    @form-delete.window="deleteItem('form_'+$event.detail.formId); $wire.deleteForm($event.detail.formId);"
    @sense-delete.window="deleteItem('sense_'+$event.detail.senseId); $wire.deleteSense($event.detail.senseId);"
    @subsense-delete.window="deleteItem('subsense_'+$event.detail.subsenseId); $wire.deleteSubsense($event.detail.subsenseId);"
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
                            modelIsAlpine="false"
                            @change="$wire.updatePrimaryForm($el.value);"
                        >
                            <option value="" class="cursor-pointer italic">{{ __('tollerus::ui.none') }}</option>
                            @foreach (collect($infoForm['lexemes'])->sortBy('position') as $lexemeId => $lexeme)
                                @if (count($lexeme['forms']) > 0)
                                    <optgroup
                                        id="primary_form_optgroup_{{ $lexemeId }}"
                                        wire:key="primary-form-optgroup-{{ $lexemeId }}"
                                        label="{{ $lexeme['wordClassName'] }}"
                                    >
                                        @foreach ($lexeme['forms'] as $formId => $form)
                                            <option
                                                id="primary_form_option_{{ $formId }}"
                                                wire:key="primary-form-option-{{ $formId }}"
                                                value="{{ $formId }}"
                                                class="cursor-pointer"
                                            >{{ $form['transliterated'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </x-tollerus::inputs.select>
                    </div>
                    @if (empty($infoForm['primaryForm']))
                        <x-tollerus::alert type="warning">
                            <p>{{ __('tollerus::ui.missing_primary_form_alert') }}</p>
                        </x-tollerus::alert>
                    @endif
                </div>
                <div class="w-full flex flex-col gap-2" @tollerus-wysiwyg-input="btn = 'save'; dirty = true;">
                    <h3 class="font-bold text-lg">
                        <label for="etym" class="flex flex-row gap-4 items-center">
                            <x-tollerus::icons.academic-cap />
                            <span>{{ __('tollerus::ui.word_origin') }}</span>
                        </label>
                    </h3>
                    <x-tollerus::inputs.textarea
                        wysiwyg="true"
                        wysiwygIsInline="true"
                        :nativeKeyboards="$nativeKeyboards"
                        :language="$language"
                        id="etym"
                        model="infoForm.etym"
                        @input="$dispatch('tollerus-wysiwyg-input')"
                    />
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
                @foreach (collect($infoForm['lexemes'])->sortBy('position') as $lexemeId => $lexeme)
                    @php
                        $prevNeighborId = $this->getNeighborId($infoForm['lexemes'], $lexemeId, -1);
                        $nextNeighborId = $this->getNeighborId($infoForm['lexemes'], $lexemeId, +1);
                        $wordClassGroup = collect($wordClassGroups)->firstWhere('id', $lexeme['wordClassGroupId']);
                    @endphp
                    <div
                        id="lexeme_{{ $lexemeId }}"
                        wire:key="lexeme-{{ $lexemeId }}"
                        data-obj="lexeme"
                        class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                        style="order: {{ $loop->index }}"
                        @transitionend="$nextTick(() => {animating=false});"
                    >
                        <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_word_class_up') }}"
                                x-bind:disabled="animating || {{ $this->isFirstItem($infoForm['lexemes'], $lexemeId) ? 'true' : 'false' }}"
                                @click="animating=true; moveLexeme($el.closest('[data-obj=lexeme]'), {{ $lexemeId }}, {{ $prevNeighborId ?? 'null' }});"
                            >
                                <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                <span class="sr-only">{{ __('tollerus::ui.move_word_class_up') }}</span>
                            </x-tollerus::inputs.button>
                            <x-tollerus::inputs.button
                                type="inverse"
                                title="{{ __('tollerus::ui.move_word_class_down') }}"
                                x-bind:disabled="animating || {{ $this->isLastItem($infoForm['lexemes'], $lexemeId) ? 'true' : 'false' }}"
                                @click="animating=true; moveLexeme($el.closest('[data-obj=lexeme]'), {{ $lexemeId }}, {{ $nextNeighborId ?? 'null' }});"
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
                                        @if (empty($lexeme['wordClassName']))
                                            <span class="font-normal italic">{{ __('tollerus::ui.word_class_nameless') }}</span>
                                        @else
                                            <span>{{ $lexeme['wordClassName'] }}</span>
                                        @endif
                                    </h2>
                                    <div class="flex flex-row gap-4 items-center">
                                        <span>{{ __('tollerus::ui.public_id') }}</span>
                                        <span class="font-mono">{{ $lexeme['globalId'] }}</span>
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
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'lexeme-delete', payload: {lexemeId: {{ $lexemeId }}} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => __('tollerus::ui.word_class')]) }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                            @if ($lexeme['hasMissingForms'])
                                <x-tollerus::alert type="warning">
                                    <p>{{ __('tollerus::ui.missing_forms_alert') }}</p>
                                    <x-tollerus::inputs.button
                                        type="primary"
                                        size="small"
                                        title="{{ __('tollerus::ui.add_missing_word_forms') }}"
                                        @click="$wire.createMissingForms({{ $lexemeId }});"
                                        wire:loading.attr="disabled"
                                        wire:target="createMissingForms"
                                        class="px-2"
                                    >
                                        <span>{{ __('tollerus::ui.add_missing_word_forms') }}</span>
                                    </x-tollerus::inputs.button>
                                </x-tollerus::alert>
                            @endif
                            <x-tollerus::pane class="flex flex-col gap-4 items-start">
                                <div class="flex flex-col md:flex-row gap-y-4 gap-x-8 items-start md:items-center">
                                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                                        <x-tollerus::icons.fingerprint />
                                        <span>{{ __('tollerus::ui.word_forms') }}</span>
                                    </h3>
                                    @if ($lexeme['wasMatched'] && count($wordClassGroup['features']) > 0)
                                        <x-tollerus::button
                                            type="secondary"
                                            size="small"
                                            title="{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}"
                                            href="{{ $lexeme['inflectionEditUrl'] }}"
                                            class="flex flex-row gap-2 items-center px-2"
                                        >
                                            <x-tollerus::icons.edit />
                                            <span>{{ __('tollerus::ui.edit_thing', ['thing' => __('tollerus::ui.inflection_tables')]) }}</span>
                                        </x-tollerus::button>
                                    @endif
                                </div>
                                @foreach ($lexeme['forms'] as $formId => $form)
                                    <x-tollerus::panel
                                        id="form_{{ $formId }}"
                                        wire:key="form-{{ $formId }}"
                                        class="flex flex-col gap-4 items-start w-full"
                                    >
                                        <div class="flex flex-row gap-4 justify-between items-center w-full">
                                            <div class="flex flex-row gap-4 items-center">
                                                <span>{{ __('tollerus::ui.public_id') }}</span>
                                                <span class="font-mono">{{ $form['globalId'] }}</span>
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
                                                        { text: msgs.yes_delete, type: 'primary', clickEvent: 'form-delete', payload: {formId: {{ $formId }}} }
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
                                                    idExpression="'form_{{ $formId }}_transliterated'"
                                                    model="infoForm.lexemes.{{ $lexemeId }}.forms.{{ $formId }}.transliterated"
                                                    fieldName="{{ config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated')) }}"
                                                    showLabel="true"
                                                    saveEvent="$wire.updateForm({{ $lexemeId }}, {{ $formId }}, 'transliterated', prop, fieldKey, id);"
                                                >
                                                    <x-slot:before>
                                                        @if ($form['canAutoInflect'])
                                                            @if ($form['srcForm']===null || empty($lexeme['forms'][$form['srcForm']]['transliterated']))
                                                                <x-tollerus::inputs.button
                                                                    type="secondary"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                    disabled
                                                                >
                                                                    <x-tollerus::icons.bolt fill="currentColor" />
                                                                    <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            @else
                                                                <x-tollerus::inputs.button
                                                                    type="secondary"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                    @click="$wire.autoInflect({{ $lexemeId }}, {{ $formId }}, {{ $form['matchingRowId'] }}, '{{ $lexeme['forms'][$form['srcForm']]['transliterated'] }}', 'transliterated', null, id);"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="autoInflect"
                                                                >
                                                                    <x-tollerus::icons.bolt fill="currentColor" />
                                                                    <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            @endif
                                                        @endif
                                                    </x-slot:before>
                                                </x-tollerus::inputs.text-saveable>
                                            </div>
                                            <div class="lg:w-80" data-keyboard-elem="territory">
                                                <x-tollerus::inputs.text-saveable
                                                    idExpression="'form_{{ $formId }}_phonemic'"
                                                    model="infoForm.lexemes.{{ $lexemeId }}.forms.{{ $formId }}.phonemic"
                                                    fieldName="{{ __('tollerus::ui.phonemic') }}"
                                                    showLabel="true"
                                                    saveEvent="$wire.updateForm({{ $lexemeId }}, {{ $formId }}, 'phonemic', prop, fieldKey, id);"
                                                >
                                                    <x-slot:before>
                                                        @if ($form['canAutoInflect'])
                                                            @if ($form['srcForm']===null || empty($lexeme['forms'][$form['srcForm']]['phonemic']))
                                                                <x-tollerus::inputs.button
                                                                    type="secondary"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                    disabled
                                                                >
                                                                    <x-tollerus::icons.bolt fill="currentColor" />
                                                                    <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            @else
                                                                <x-tollerus::inputs.button
                                                                    type="secondary"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                    @click="$wire.autoInflect({{ $lexemeId }}, {{ $formId }}, {{ $form['matchingRowId'] }}, '{{ $lexeme['forms'][$form['srcForm']]['phonemic'] }}', 'phonemic', null, id);"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="autoInflect"
                                                                >
                                                                    <x-tollerus::icons.bolt fill="currentColor" />
                                                                    <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            @endif
                                                        @endif
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
                                            <div class="flex flex-col items-start">
                                                <x-tollerus::inputs.checkbox
                                                    idExpression="'form_{{ $formId }}_irregular'"
                                                    model="infoForm.lexemes.{{ $lexemeId }}.forms.{{ $formId }}.irregular"
                                                    modelIsAlpine="false"
                                                    label="{{ __('tollerus::ui.irregular') }}"
                                                    @change="$wire.updateForm({{ $lexemeId }}, {{ $formId }}, 'irregular', $el.checked, 'infoForm.lexemes.{{ $lexemeId }}.forms.{{ $formId }}.irregular', id);"
                                                />
                                            </div>
                                        </div>
                                        <table>
                                            <tbody>
                                                @foreach ($form['nativeSpellings'] as $i => $nativeSpelling)
                                                    <tr wire:key="form-{{ $formId }}-native-spelling-{{ $nativeSpelling['neographyId'] }}">
                                                        <th scope="row" class="font-normal text-right pr-2 py-1">{{ $nativeSpelling['neographyName'] }}</th>
                                                        <td class="text-left pr-2 py-1 w-120" data-keyboard-elem="territory">
                                                            <x-tollerus::inputs.text-saveable
                                                                idExpression="'form_{{ $formId }}_native_spelling_{{ $nativeSpelling['neographyId'] }}'"
                                                                model="infoForm.lexemes.{{ $lexemeId }}.forms.{{ $formId }}.nativeSpellings.{{ $i }}.spelling"
                                                                fieldName="{{ __('tollerus::ui.native_spelling') }}"
                                                                saveEvent="$wire.updateNativeSpelling({{ $lexemeId }}, {{ $formId }}, {{ $nativeSpelling['neographyId'] }}, prop, id);"
                                                                class="tollerus_{{ $nativeSpelling['neographyMachineName'] }}"
                                                            >
                                                                <x-slot:before>
                                                                    @if ($form['canAutoInflect'])
                                                                        @php
                                                                            $srcSpelling = collect($lexeme['forms'][$form['srcForm']]['nativeSpellings'])->firstWhere('neographyId', $nativeSpelling['neographyId']);
                                                                        @endphp
                                                                        @if (empty($srcSpelling['spelling']))
                                                                            <x-tollerus::inputs.button
                                                                                type="secondary"
                                                                                size="small"
                                                                                class="align-middle"
                                                                                title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                                disabled
                                                                            >
                                                                                <x-tollerus::icons.bolt fill="currentColor" />
                                                                                <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                            </x-tollerus::inputs.button>
                                                                        @else
                                                                            <x-tollerus::inputs.button
                                                                                type="secondary"
                                                                                size="small"
                                                                                class="align-middle"
                                                                                title="{{ __('tollerus::ui.auto_inflect') }}"
                                                                                @click="$wire.autoInflect({{ $lexemeId }}, {{ $formId }}, {{ $form['matchingRowId'] }}, '{{ $srcSpelling['spelling'] }}', 'native', '{{ $nativeSpelling['neographyId'] }}', id);"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="autoInflect"
                                                                            >
                                                                                <x-tollerus::icons.bolt fill="currentColor" />
                                                                                <label class="sr-only">{{ __('tollerus::ui.auto_inflect') }}</label>
                                                                            </x-tollerus::inputs.button>
                                                                        @endif
                                                                    @endif
                                                                    @if (count($nativeKeyboards[$nativeSpelling['neographyId']]['keyboards']) > 0)
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
                                                                                            neographySubset: ['{{ $nativeSpelling['neographyId'] }}'],
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
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if (count($wordClassGroup['features']) > 0)
                                            <div class="flex flex-col gap-4 items-start w-full">
                                                <h4>{{ __('tollerus::ui.inflection_values') }}</h4>
                                                <div class="pl-12 flex flex-col gap-2 items-start w-full">
                                                    <ul class="flex flex-row flex-wrap gap-2">
                                                        @foreach ($form['inflectionValues'] as $valueId => $value)
                                                            <li
                                                                id="value_{{ $valueId }}"
                                                                wire:key="value-{{ $valueId }}"
                                                                class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm flex flex-row gap-1 items-center p-1"
                                                            >
                                                                <span>{{ $value['featureName'] }}: {{ $value['valueName'] }}</span>
                                                                <x-tollerus::inputs.button
                                                                    type="inverse"
                                                                    size="small"
                                                                    class="align-middle"
                                                                    title="{{ __('tollerus::ui.remove_value') }}"
                                                                    @click="$wire.removeFormValue({{ $formId }}, {{ $value['valueId'] }});"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="removeFormValue"
                                                                >
                                                                    <x-tollerus::icons.x/>
                                                                    <label class="sr-only">{{ __('tollerus::ui.remove_value') }}</label>
                                                                </x-tollerus::inputs.button>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    <div class="flex flex-col md:flex-row gap-4 justify-start items-start md:items-center">
                                                        @if ($form['matchingRowId'] === null)
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
                                                                @foreach ($wordClassGroup['columns'] as $column)
                                                                    <div
                                                                        id="match_form_column_{{ $column['id'] }}"
                                                                        wire:key="match-form-column-{{ $column['id'] }}"
                                                                        class="flex flex-col items-start"
                                                                    >
                                                                        <span class="italic opacity-50">{{ $column['label'] }}</span>
                                                                        @foreach ($column['rows'] as $row)
                                                                            <x-tollerus::inputs.button
                                                                                wire:key="match-form-row-{{ $rowId }}"
                                                                                type="inverse"
                                                                                size="small"
                                                                                @click="open=false; $wire.matchFormToRow({{ $lexemeId }}, {{ $formId }}, {{ $column['id'] }}, {{ $row['id'] }});"
                                                                                class="ml-4"
                                                                            >{{ $row['label'] }}</x-tollerus::inputs.button>
                                                                        @endforeach
                                                                    </div>
                                                                @endforeach
                                                            </x-tollerus::inputs.dropdown>
                                                        @endif
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
                                                            @foreach ($wordClassGroup['features'] as $feature)
                                                                <div
                                                                    id="add_value_feature_{{ $feature['id'] }}"
                                                                    wire:key="add-value-feature-{{ $feature['id'] }}"
                                                                    class="flex flex-col items-start"
                                                                >
                                                                    <span class="italic opacity-50">{{ $feature['name'] }}</span>
                                                                    @foreach ($feature['values'] as $value)
                                                                        @if (collect($form['inflectionValues'])->pluck('featureId')->contains($feature['id']))
                                                                            <x-tollerus::inputs.button
                                                                                wire:key="add-value-value-{{ $value['id'] }}"
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4 line-through"
                                                                                disabled
                                                                            >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                                        @else
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                size="small"
                                                                                class="ml-4"
                                                                                @click="open=false; $wire.addFormValue({{ $lexemeId }}, {{ $formId }}, {{ $value['id'] }});"
                                                                            >{{ $value['name'] }}</x-tollerus::inputs.button>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            @endforeach
                                                        </x-tollerus::inputs.dropdown>
                                                    </div>
                                                </div>
                                                @if ($form['matchingRowId'] !== null)
                                                    <p class="flex flex-row gap-3 justify-start items-center text-zinc-500 dark:text-zinc-500">
                                                        <x-tollerus::icons.check class="border-2 border-zinc-500 rounded-full"/>
                                                        <span class="italic">{{ __('tollerus::ui.matched_inflection_row') }}:</span>
                                                        <span class="font-bold">{{ $form['matchingRowLabel'] }}</span>
                                                    </p>
                                                @endif
                                                @if ($lexeme['wasMatched'])
                                                    @if ($form['matchingRowCount'] < 1)
                                                        <x-tollerus::alert type="warning">
                                                            <p>{{ __('tollerus::ui.no_row_matches_alert') }}</p>
                                                        </x-tollerus::alert>
                                                    @elseif ($form['matchingRowCount'] > 1)
                                                        <x-tollerus::alert type="warning">
                                                            <p>{{ __('tollerus::ui.multiple_row_matches_alert') }}</p>
                                                        </x-tollerus::alert>
                                                    @endif
                                                    @if ($form['matchingRowHasOthers'])
                                                        <x-tollerus::alert type="warning">
                                                            <p>{{ __('tollerus::ui.multiple_form_matches_alert') }}</p>
                                                        </x-tollerus::alert>
                                                    @endif
                                                @endif
                                            </div>
                                        @endif
                                        @if (empty($form['transliterated']))
                                            <x-tollerus::alert type="warning">
                                                <p>{{ __('tollerus::ui.word_form_not_transliterated_alert', ['transliteration' => Config::get('tollerus.local_transliteration_word', __('tollerus::ui.transliteration'))]) }}</p>
                                            </x-tollerus::alert>
                                        @endif
                                        @if (count($wordClassGroup['features']) == 0 && $infoForm['primaryForm'] !== null && $infoForm['primaryForm'] != $formId)
                                            <x-tollerus::alert type="warning">
                                                <p>{{ __('tollerus::ui.non_primary_form_alert') }}</p>
                                            </x-tollerus::alert>
                                        @endif
                                    </x-tollerus::panel>
                                @endforeach
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_word_form') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createForm({{ $lexemeId }});"
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
                                @if (count($lexeme['senses']) > 0)
                                    <div class="flex flex-col gap-4 items-start w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                        @foreach (collect($lexeme['senses'])->sortBy('num') as $senseId => $sense)
                                            @php
                                                $prevNeighborId = $this->getNeighborId($lexeme['senses'], $senseId, -1, 'num');
                                                $nextNeighborId = $this->getNeighborId($lexeme['senses'], $senseId, +1, 'num');
                                            @endphp
                                            <div
                                                id="sense_{{ $senseId }}"
                                                wire:key="sense-{{ $senseId }}"
                                                data-obj="sense"
                                                class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                                                style="order: {{ $loop->index }}"
                                                @transitionend="$nextTick(() => {animating=false});"
                                            >
                                                <x-tollerus::panel class="px-3 py-8 flex flex-col gap-6 justify-start shrink-0 rounded-l-xl rounded-r-none">
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_sense_up') }}"
                                                        x-bind:disabled="animating || {{ $this->isFirstItem($lexeme['senses'], $senseId, 'num') ? 'true' : 'false' }}"
                                                        @click="animating=true; moveSense({{ $lexemeId }}, $el.closest('[data-obj=sense]'), {{ $senseId }}, {{ $prevNeighborId ?? 'null' }});"
                                                    >
                                                        <x-tollerus::icons.chevron-up class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_sense_up') }}</span>
                                                    </x-tollerus::inputs.button>
                                                    <x-tollerus::inputs.button
                                                        type="inverse"
                                                        title="{{ __('tollerus::ui.move_sense_down') }}"
                                                        x-bind:disabled="animating || {{ $this->isLastItem($lexeme['senses'], $senseId, 'num') ? 'true' : 'false' }}"
                                                        @click="animating=true; moveSense({{ $lexemeId }}, $el.closest('[data-obj=sense]'), {{ $senseId }}, {{ $nextNeighborId ?? 'null' }});"
                                                    >
                                                        <x-tollerus::icons.chevron-down class="h-8 w-8" />
                                                        <span class="sr-only">{{ __('tollerus::ui.move_sense_down') }}</span>
                                                    </x-tollerus::inputs.button>
                                                </x-tollerus::panel>
                                                <x-tollerus::panel class="flex flex-col gap-4 items-start rounded-l-none flex-grow">
                                                    <div class="flex flex-row gap-4 justify-between items-start w-full">
                                                        <div class="flex-grow flex flex-row gap-4 justify-start items-center">
                                                            <h4 class="font-bold text-lg">
                                                                <span>{{ $sense['num'] }}.</span>
                                                            </h4>
                                                            <div>
                                                                <x-tollerus::inputs.text-saveable
                                                                    idExpression="'sense_{{ $senseId }}_usage'"
                                                                    model="infoForm.lexemes.{{ $lexemeId }}.senses.{{ $senseId }}.usage"
                                                                    fieldName="{{ __('tollerus::ui.usage_note') }}"
                                                                    showLabel="true"
                                                                    saveEvent="$wire.updateSense({{ $lexemeId }}, {{ $senseId }}, 'usage', prop, id);"
                                                                />
                                                            </div>
                                                        </div>
                                                        <x-tollerus::inputs.button
                                                            type="inverse"
                                                            size="small"
                                                            class="align-middle shrink-0"
                                                            title="{{ __('tollerus::ui.delete_word_sense') }}"
                                                            @click="$dispatch('open-modal', {
                                                                message: msgs['delete_sense_confirmation'],
                                                                buttons: [
                                                                    { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                    { text: msgs.yes_delete, type: 'primary', clickEvent: 'sense-delete', payload: {senseId: {{ $senseId }}} }
                                                                ]
                                                            });"
                                                        >
                                                            <x-tollerus::icons.delete/>
                                                            <label class="sr-only">{{ __('tollerus::ui.delete_word_sense') }}</label>
                                                        </x-tollerus::inputs.button>
                                                    </div>
                                                    <div
                                                        data-obj="textarea-div"
                                                        class="flex flex-col gap-2 items-start w-full"
                                                        x-data="{ dirty: false, btn: 'saved', id: 'sense_{{ $senseId }}_body', }"
                                                        @tollerus-wysiwyg-input="btn = 'save'; dirty = true;"
                                                    >
                                                        <x-tollerus::inputs.textarea
                                                            id="sense_{{ $senseId }}_body"
                                                            wysiwyg="true"
                                                            wysiwygIsInline="true"
                                                            :nativeKeyboards="$nativeKeyboards"
                                                            :language="$language"
                                                            model="infoForm.lexemes.{{ $lexemeId }}.senses.{{ $senseId }}.body"
                                                            @input="$dispatch('tollerus-wysiwyg-input')"
                                                        />
                                                        <x-tollerus::inputs.button
                                                            @click="
                                                                btn = 'saving';
                                                                $wire.updateSense({{ $lexemeId }}, {{ $senseId }}, 'body', document.getElementById(id).value, id);
                                                            "
                                                            x-bind:disabled="!dirty"
                                                            wire:loading.attr="disabled"
                                                            wire:target="updateSense"
                                                            @sense-update-success.window="if ($event.detail.id != id) {return;} btn = 'saved'; dirty=false;"
                                                            @sense-update-failure.window="if ($event.detail.id != id) {return;} btn = 'save';"
                                                            x-text="msgs[btn]" />
                                                    </div>
                                                    <x-tollerus::drawer open="false" rootClass="w-full" class="flex flex-col gap-4 w-full">
                                                        <x-slot:heading-button>
                                                            <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-zinc-500 dark:bg-zinc-400 group-has-hover:bg-zinc-400 group-has-hover:dark:bg-zinc-300 text-white dark:text-zinc-800">
                                                                <span>{{ __('tollerus::ui.subsenses') }}</span>
                                                                @if (count($sense['subsenses']) > 0)
                                                                    <span class="block font-bold text-zinc-500 dark:text-zinc-400 bg-white dark:bg-zinc-800 rounded-full w-6 h-6 flex justify-center items-center text-center">{{ count($sense['subsenses']) }}</span>
                                                                @endif
                                                            </div>
                                                        </x-slot:heading-button>
                                                        <x-slot:heading>
                                                            <div class="flex-grow border-b-2 border-zinc-500 dark:border-zinc-400"></div>
                                                        </x-slot:heading>
                                                        @if (count($sense['subsenses']) > 0)
                                                            <div class="flex flex-col gap-4 items-start pl-12 w-full" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
                                                                @foreach (collect($sense['subsenses'])->sortBy('num') as $subsenseId => $subsense)
                                                                    @php
                                                                        $prevNeighborId = $this->getNeighborId($sense['subsenses'], $subsenseId, -1, 'num');
                                                                        $nextNeighborId = $this->getNeighborId($sense['subsenses'], $subsenseId, +1, 'num');
                                                                    @endphp
                                                                    <div
                                                                        id="subsense_{{ $subsenseId }}"
                                                                        wire:key="subsense-{{ $subsenseId }}"
                                                                        data-obj="subsense"
                                                                        class="flex flex-row gap-2 w-full items-stretch transition-[transform] duration-500 ease-out"
                                                                        style="order: {{ $loop->index }}"
                                                                        @transitionend="$nextTick(() => {animating=false});"
                                                                    >
                                                                        <div class="flex flex-col justify-start shrink-0">
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                title="{{ __('tollerus::ui.move_subsense_up') }}"
                                                                                x-bind:disabled="animating || {{ $this->isFirstItem($sense['subsenses'], $subsenseId, 'num') ? 'true' : 'false' }}"
                                                                                @click="animating=true; moveSubsense({{ $lexemeId }}, {{ $senseId }}, $el.closest('[data-obj=subsense]'), {{ $subsenseId }}, {{ $prevNeighborId ?? 'null' }});"
                                                                            >
                                                                                <x-tollerus::icons.chevron-up class="h-6 w-6" />
                                                                                <span class="sr-only">{{ __('tollerus::ui.move_subsense_up') }}</span>
                                                                            </x-tollerus::inputs.button>
                                                                            <x-tollerus::inputs.button
                                                                                type="inverse"
                                                                                title="{{ __('tollerus::ui.move_subsense_down') }}"
                                                                                x-bind:disabled="animating || {{ $this->isLastItem($sense['subsenses'], $subsenseId, 'num') ? 'true' : 'false' }}"
                                                                                @click="animating=true; moveSubsense({{ $lexemeId }}, {{ $senseId }}, $el.closest('[data-obj=subsense]'), {{ $subsenseId }}, {{ $nextNeighborId ?? 'null' }});"
                                                                            >
                                                                                <x-tollerus::icons.chevron-down class="h-6 w-6" />
                                                                                <span class="sr-only">{{ __('tollerus::ui.move_subsense_down') }}</span>
                                                                            </x-tollerus::inputs.button>
                                                                        </div>
                                                                        <div
                                                                            data-obj="textarea-div"
                                                                            class="flex flex-col gap-2 items-start flex-grow"
                                                                            x-data="{ dirty: false, btn: 'saved', id: 'subsense_{{ $subsenseId }}_body' }"
                                                                            @tollerus-wysiwyg-input="btn = 'save'; dirty = true;"
                                                                        >
                                                                            <div class="flex flex-row justify-between items-center w-full">
                                                                                <div class="flex-grow flex flex-row justify-start items-center">
                                                                                    <x-tollerus::inputs.text-saveable
                                                                                        idExpression="'subsense_{{ $subsenseId }}_usage'"
                                                                                        model="infoForm.lexemes.{{ $lexemeId }}.senses.{{ $senseId }}.subsenses.{{ $subsenseId }}.usage"
                                                                                        fieldName="{{ __('tollerus::ui.usage_note') }}"
                                                                                        showLabel="true"
                                                                                        saveEvent="$wire.updateSubsense({{ $lexemeId }}, {{ $senseId }}, {{ $subsenseId }}, 'usage', prop, id);"
                                                                                    />
                                                                                </div>
                                                                                <x-tollerus::inputs.button
                                                                                    type="inverse"
                                                                                    size="small"
                                                                                    class="align-middle shrink-0"
                                                                                    title="{{ __('tollerus::ui.delete_subsense') }}"
                                                                                    @click="$dispatch('open-modal', {
                                                                                        message: msgs['delete_subsense_confirmation'],
                                                                                        buttons: [
                                                                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                                                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'subsense-delete', payload: {subsenseId: {{ $subsenseId }}} }
                                                                                        ]
                                                                                    });"
                                                                                >
                                                                                    <x-tollerus::icons.delete/>
                                                                                    <label class="sr-only">{{ __('tollerus::ui.delete_subsense') }}</label>
                                                                                </x-tollerus::inputs.button>
                                                                            </div>
                                                                            <x-tollerus::inputs.textarea
                                                                                id="subsense_{{ $subsenseId }}_body"
                                                                                wysiwyg="true"
                                                                                wysiwygIsInline="true"
                                                                                :nativeKeyboards="$nativeKeyboards"
                                                                                :language="$language"
                                                                                model="infoForm.lexemes.{{ $lexemeId }}.senses.{{ $senseId }}.subsenses.{{ $subsenseId }}.body"
                                                                                @input="$dispatch('tollerus-wysiwyg-input', {html: editor.getHTML()})"
                                                                            />
                                                                            <x-tollerus::inputs.button
                                                                                size="small"
                                                                                @click="
                                                                                    btn = 'saving';
                                                                                    $wire.updateSubsense({{ $lexemeId }}, {{ $senseId }}, {{ $subsenseId }}, 'body', document.getElementById(id).value, id);
                                                                                "
                                                                                x-bind:disabled="!dirty"
                                                                                wire:loading.attr="disabled"
                                                                                wire:target="updateSubsense"
                                                                                @subsense-update-success.window="if ($event.detail.id != id) {return;} btn = 'saved'; dirty=false;"
                                                                                @subsense-update-failure.window="if ($event.detail.id != id) {return;} btn = 'save';"
                                                                                x-text="msgs[btn]" />
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <div class="pl-12">
                                                            <x-tollerus::inputs.missing-data
                                                                size="small"
                                                                title="{{ __('tollerus::ui.add_subsense') }}"
                                                                class="relative flex flex-row gap-2 justify-center items-center w-full"
                                                                @click="$wire.createSubsense({{ $lexemeId }}, {{ $senseId }});"
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
                                        @endforeach
                                    </div>
                                @endif
                                <x-tollerus::inputs.missing-data
                                    size="small"
                                    title="{{ __('tollerus::ui.add_word_sense') }}"
                                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                                    @click="$wire.createSense({{ $lexemeId }});"
                                    wire:loading.attr="disabled"
                                    wire:target="createSense"
                                >
                                    <x-tollerus::icons.plus/>
                                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_sense') }}</span>
                                </x-tollerus::inputs.missing-data>
                            </x-tollerus::pane>
                        </x-tollerus::panel>
                    </div>
                @endforeach
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
                    @foreach ($wordClassGroups as $wordClassGroup)
                        <div
                            wire:key="add-lexeme-wordclassgroup-{{ $wordClassGroup['id'] }}"
                            class="flex flex-col items-start"
                        >
                            <span class="italic opacity-50">{{ __('tollerus::ui.group_nameless') }}</span>
                            @foreach ($wordClassGroup['classes'] as $wordClass)
                                @if (collect($infoForm['lexemes'])->pluck('wordClassId')->contains($wordClass['id']))
                                    <x-tollerus::inputs.button
                                        wire:key="add-lexeme-wordclass-{{ $wordClass['id'] }}"
                                        type="inverse"
                                        size="small"
                                        class="ml-4 line-through"
                                        disabled
                                    >{{ $wordClass['name'] }}</x-tollerus::inputs.button>
                                @else
                                    <x-tollerus::inputs.button
                                        wire:key="add-lexeme-wordclass-{{ $wordClass['id'] }}"
                                        type="inverse"
                                        size="small"
                                        class="ml-4"
                                        @click="open=false; $wire.createLexeme({{ $wordClass['id'] }});"
                                    >{{ $wordClass['name'] }}</x-tollerus::inputs.button>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </x-tollerus::inputs.dropdown>
            </div>
        </div>
    </div>
    <x-tollerus::modal/>
    @if (count($nativeKeyboards) > 0)
        <x-tollerus::keyboards.native :nativeKeyboards="$nativeKeyboards"/>
    @endif
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
