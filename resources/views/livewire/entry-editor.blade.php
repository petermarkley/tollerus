<div
    x-data="{
        dirty: false,
        btn: 'saved',
        msgs: {
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
        },
        infoForm: $wire.entangle('infoForm'),
    }"
    @modal-discard.window="$wire.refreshForm(); dirty=false;"
    @modal-save.window="$wire.save(tab, '', {});"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ mb_ucfirst($entry->primaryForm->transliterated) }}</span>
        </h1>
        <div class="flex flex-col gap-6">
            <x-tollerus::panel>
                Lorem ipsum dolor sit amet.
            </x-tollerus::panel>
        </div>
    </div>
    <x-tollerus::modal/>
</div>
<x-tollerus::reorder-script/>
