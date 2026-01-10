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
        },
        infoForm: $wire.entangle('infoForm'),
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
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0 flex flex-row gap-4 justify-between items-center">
            <span>{{ mb_ucfirst($entry->primaryForm->transliterated) }}</span>
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
                            <h2 class="flex flex-row gap-2 items-center justify-between">
                                <div class="font-bold text-xl flex flex-row gap-2 items-center">
                                    <x-tollerus::icons.lightbulb class="h-8"/>
                                    <span x-text="lexeme.wordClassName" x-bind:class="{ 'font-normal italic': lexeme.wordClassName.length==0 }"></span>
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
                            </h2>
                            <div>
                                Lorem ipsum
                            </div>
                        </x-tollerus::panel>
                    </div>
                </template>
            </div>
            <div class="px-6 xl:px-0">
                <x-tollerus::inputs.missing-data
                    size="medium" floating="true"
                    title="{{ __('tollerus::ui.add_word_class') }}"
                    class="relative flex flex-row gap-2 justify-center items-center w-full"
                    @click="$wire.createLexeme();"
                    wire:loading.attr="disabled"
                    wire:target="createLexeme"
                >
                    <x-tollerus::icons.plus/>
                    <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_word_class') }}</span>
                </x-tollerus::inputs.missing-data>
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
