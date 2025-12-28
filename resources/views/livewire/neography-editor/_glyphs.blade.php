<div id="tabpanel-glyphs" role="tabpanel" x-cloak x-show="tab=='glyphs'" class="flex flex-col gap-6 border-t-4 border-white dark:border-zinc-800 pt-4">
    <h1 class="font-bold text-2xl px-6 xl:px-0">
        <span>{{ __('tollerus::ui.sections') }}</span>
    </h1>
    <template x-if="Object.keys(glyphsForm).length == 0">
        <div class="flex flex-col gap-4 items-start w-full" x-data="{ btn: 'extract_from_svg' }">
            <x-tollerus::alert>
                <template x-if="fontForm.{{ \PeterMarkley\Tollerus\Enums\FontFormat::Svg->value }}.blobExists">
                    <p class="m-0">{{ __('tollerus::ui.svg_to_glyphs_notice') }}</p>
                </template>
                <template x-if="!fontForm.{{ \PeterMarkley\Tollerus\Enums\FontFormat::Svg->value }}.blobExists">
                    <div>{!! Str::markdown(__('tollerus::ui.svg_to_glyphs_notice_no_font', [
                        'font_url' => route('tollerus.admin.neographies.edit.tab', ['neography' => $neography, 'tab' => 'font'])
                    ])) !!}</div>
                </template>
            </x-tollerus::alert>
            <x-tollerus::inputs.button
                x-text="msgs[btn]"
                @click="btn = 'extracting'; $wire.extractSvgToGlyphs();"
                @svgtoglyphs-failure.window="btn = 'extract_from_svg';"
                @svgtoglyphs-success.window="btn = 'extract_from_svg';"
                x-bind:disabled="!fontForm.{{ \PeterMarkley\Tollerus\Enums\FontFormat::Svg->value }}.blobExists"
                wire:loading.attr="disabled"
                wire:target="extractSvgToGlyphs"
            />
        </div>
    </template>
    <template x-if="Object.keys(glyphsForm).length > 0">
        <div class="flex flex-col gap-6" x-data="{ animating: false }" x-bind:class="{ 'pointer-events-none': animating }">
            <template x-for="([sectId, sect], i) in $store.reorderFunctions.sortItems(glyphsForm)">
                <div
                    x-bind:id="'sect_' + sectId"
                    data-obj="sect"
                    class="flex flex-row gap-[1px] w-full items-stretch transition-[transform] duration-500 ease-out"
                    x-bind:style="'order: '+i"
                    @transitionend="$nextTick(() => {animating=false});"
                >
                    <x-tollerus::panel class="px-3 py-12 flex flex-col gap-6 justify-start shrink-0 rounded-l-full rounded-r-none">
                        <x-tollerus::inputs.button
                            type="inverse"
                            title="{{ __('tollerus::ui.move_section_up') }}"
                            x-bind:disabled="animating || $store.reorderFunctions.isFirstItem(glyphsForm, sectId)"
                            @click="animating=true; moveSection($el.closest('[data-obj=&quot;sect&quot;]'), sectId, -1);"
                        >
                            <x-tollerus::icons.chevron-up class="h-8 w-8" />
                            <span class="sr-only">{{ __('tollerus::ui.move_section_up') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            type="inverse"
                            title="{{ __('tollerus::ui.move_section_down') }}"
                            x-bind:disabled="animating || $store.reorderFunctions.isLastItem(glyphsForm, sectId)"
                            @click="animating=true; moveSection($el.closest('[data-obj=&quot;sect&quot;]'), sectId, +1);"
                        >
                            <x-tollerus::icons.chevron-down class="h-8 w-8" />
                            <span class="sr-only">{{ __('tollerus::ui.move_section_down') }}</span>
                        </x-tollerus::inputs.button>
                    </x-tollerus::panel>
                    <x-tollerus::panel class="flex flex-col gap-6 flex-grow rounded-l-none">
                        <h2 class="flex flex-row gap-2 items-center justify-between">
                            <a
                                class="text-zinc-900 dark:text-zinc-300 font-bold text-xl flex flex-row gap-2 items-center"
                                x-bind:title="sect.editUrlText"
                                x-bind:href="sect.editUrl"
                            >
                                <x-tollerus::icons.bookmark class="h-8"/>
                                <span x-text="sect.name" x-bind:class="{ 'font-normal italic': sect.name.length==0 }"></span>
                            </a>
                            <div class="flex flex-row gap-2 items-center">
                                <x-tollerus::button
                                    type="secondary"
                                    size="small"
                                    x-bind:title="sect.editUrlText"
                                    x-bind:href="sect.editUrl"
                                >
                                    <x-tollerus::icons.edit class="h-6 w-6"/>
                                    <span class="sr-only" x-text="sect.editUrlText"></span>
                                </x-tollerus::button>
                                <x-tollerus::inputs.button
                                    type="secondary"
                                    size="small"
                                    title="{{ __('tollerus::ui.delete_section') }}"
                                    @click="$dispatch('open-modal', {
                                        message: msgs['delete_section_confirmation'],
                                        buttons: [
                                            { text: msgs.no_cancel, type: 'secondary', clickEvent: 'modal-cancel' },
                                            { text: msgs.yes_delete, type: 'primary', clickEvent: 'sect-delete', payload: {sectId: sectId} }
                                        ]
                                    });"
                                >
                                    <x-tollerus::icons.delete/>
                                    <span class="sr-only">{{ __('tollerus::ui.delete_section') }}</span>
                                </x-tollerus::inputs.button>
                            </div>
                        </h2>
                        <x-tollerus::pane
                            class="w-full max-h-20 flex-grow overflow-hidden"
                            x-bind:title="sect.editUrlText"
                            x-bind:href="sect.editUrl"
                            href="#"
                        >
                            <div class="w-full max-h-20 flex-grow flex flex-col gap-2 mask-b-to-85%" x-html="sect.intro">
                            </div>
                        </x-tollerus::pane>
                    </x-tollerus::panel>
                </div>
            </template>
        </div>
    </template>
    <x-tollerus::inputs.missing-data
        size="medium"
        title="{{ __('tollerus::ui.add_section') }}"
        class="relative flex flex-row gap-2 justify-center items-center w-full"
        @click="$wire.createSection();"
        wire:loading.attr="disabled"
        wire:target="createSection"
    >
        <x-tollerus::icons.plus/>
        <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_section') }}</span>
    </x-tollerus::inputs.missing-data>
</div>
