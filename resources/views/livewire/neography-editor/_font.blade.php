<x-tollerus::panel
    id="tabpanel-font"
    role="tabpanel"
    x-cloak x-show="tab=='font'"
    class="flex flex-col gap-4"
    x-data="{ dragging: false }"
    @drop.window.prevent.stop="dragging = false;"
    @dragover.window.prevent.stop=""
    @dragenter.window.prevent.stop="dragging = true;"
    @dragleave.window.prevent.stop="dragging = false;"
>
    <template x-if="!hasFont">
        <x-tollerus::alert type="info">
            {!! Str::markdown(__('tollerus::ui.inkscape_svg_guide', [
                'guide_url' => 'https://inkscape-manuals.readthedocs.io/en/latest/creating-custom-fonts.html',
                'inkscape_url' => 'https://inkscape.org/',
                'fontforge_url' => 'https://fontforge.org/',
            ])) !!}
            {!! Str::markdown(__('tollerus::ui.ucsur_tip', [
                'pua_url' => 'https://en.wikipedia.org/wiki/Private_Use_Areas',
                'ucsur_url' => 'https://www.kreativekorp.com/ucsur/',
            ])) !!}
        </x-tollerus::alert>
    </template>
    <div x-cloak x-show="dragging" class="w-[100vw] h-[100vh] bg-black/40 backdrop-blur-sm z-20 absolute inset-0 flex justify-center items-center"></div>
    <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4 z-50">
        @foreach(\PeterMarkley\Tollerus\Enums\FontFormat::cases() as $fontFormat)
            <div class="flex flex-col gap-4 items-start">
                <h3 class="font-bold text-lg">{{ $fontFormat->localizeFormat() }}</h3>
                <template x-if="fontForm['{{ $fontFormat->value }}'].blobExists">
                    <div class="flex flex-col gap-2 items-center">
                        <div class="flex flex-col justify-center items-center">
                            <div class="relative">
                                <x-tollerus::icons.document class="w-24 h-24"/>
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    class="absolute top-0 -right-6"
                                    title="{{ __('tollerus::ui.delete_file') }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_font_file_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'font-delete', payload: {fontFormat: '{{ $fontFormat->value }}'} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_file') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </div>
                        <template x-if="fontForm['{{ $fontFormat->value }}'].published">
                            <div class="flex flex-col gap-2 items-center">
                                <div
                                    x-data="{ id: $id('font_url') }"
                                    class="flex flex-row gap-2 border border-zinc-50 dark:border-zinc-700 bg-white dark:bg-zinc-800 rounded-lg shadow p-2"
                                >
                                    <div class="relative max-w-80 whitespace-nowrap overflow-hidden p-2 rounded inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 flex flex-col items-end">
                                        <label x-bind:for="id" class="sr-only">{{ __('tollerus::ui.asset_url') }}</label>
                                        <input
                                            x-bind:id="id"
                                            type="text"
                                            x-bind:value="fontForm['{{ $fontFormat->value }}'].url"
                                            class="text-right"
                                            x-init="$nextTick(() => { $el.scrollLeft = $el.scrollWidth; });"
                                        />
                                    </div>
                                    <x-tollerus::inputs.button
                                        type="inverse"
                                        x-bind:title="(copied ? '{{ __('tollerus::ui.copied_to_clipboard') }}' : '{{ __('tollerus::ui.copy_to_clipboard') }}')"
                                        x-data="{ copied: false }"
                                        @click="if (await $store.clipboardFunctions.copy(id)) {copied=true; setTimeout(() => {copied=false;}, 2000);}"
                                        x-bind:disabled="copied"
                                    >
                                        <template x-if="!copied">
                                            <div class="relative">
                                                <x-tollerus::icons.document-duplicate class="w-6 h-6"/>
                                                <span class="sr-only">{{ __('tollerus::ui.copy_to_clipboard') }}</span>
                                            </div>
                                        </template>
                                        <template x-if="copied">
                                            <div class="relative">
                                                <x-tollerus::icons.check class="w-6 h-6"/>
                                                <span class="sr-only">{{ __('tollerus::ui.copied_to_clipboard') }}</span>
                                            </div>
                                        </template>
                                    </x-tollerus::inputs.button>
                                </div>
                                <template x-if="!fontForm['{{ $fontFormat->value }}'].valid">
                                    <x-tollerus::alert type="error">{{ __('tollerus::error.asset_invalid') }}</x-tollerus::alert>
                                </template>
                            </div>
                        </template>
                        <template x-if="!fontForm['{{ $fontFormat->value }}'].published">
                            <x-tollerus::inputs.button
                                title="{{ __('tollerus::ui.get_url') }}"
                                class="flex flex-row gap-2 items-center"
                                @click="$wire.publishFont('{{ $fontFormat->value }}');"
                                wire:loading.attr="disabled"
                                wire:target="publishFont"
                            >
                                <x-tollerus::icons.link/>
                                <span>{{ __('tollerus::ui.get_url') }}</span>
                            </x-tollerus::inputs.button>
                        </template>
                    </div>
                </template>
                <template x-if="!fontForm['{{ $fontFormat->value }}'].blobExists">
                    <div x-data="{ id: $id('file-input') }" class="flex flex-col gap-2 items-start">
                        <label
                            title="{{ __('tollerus::ui.upload_file') }}"
                            class="relative flex flex-row gap-2 justify-center items-center p-4 rounded-lg inset-shadow-sm border-dashed border-2 border-zinc-300 dark:border-zinc-500 text-sm text-zinc-500 dark:text-zinc-400 italic text-center w-80 lg:w-120 h-40 cursor-pointer bg-white dark:bg-zinc-800 hover:bg-zinc-100 hover:dark:bg-zinc-700 hover:text-zinc-500 hover:dark:text-zinc-400"
                            @drop.prevent.stop="files=$event.dataTransfer.files; if (files.length==1) {@this.upload('fontUploads.{{ $fontFormat->value }}', files[0]);} dragging=false;"
                            @dragover.prevent.stop=""
                        >
                            <x-tollerus::icons.plus/>
                            <span>{{ __('tollerus::ui.upload_file') }}</span>
                            <input x-bind:id="id" type="file" class="hidden" accept="{{ implode(', ', $fontFormat->mimeTypes()) }}" wire:model="fontUploads.{{ $fontFormat->value }}"/>
                        </label>
                        @error("fontUploads.{$fontFormat->value}")
                            <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
                        @enderror
                    </div>
                </template>
            </div>
        @endforeach
    </div>
</x-tollerus::panel>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('clipboardFunctions', {
        async copy(id) {
            let textElem = document.getElementById(id);
            try {
                await navigator.clipboard.writeText(textElem.value);
                textElem.select();
                return true;
            } catch (err) {
                console.log('Failed to copy: ', err);
                return false;
            }
        },
    });
});
</script>
@endpush
@endonce
