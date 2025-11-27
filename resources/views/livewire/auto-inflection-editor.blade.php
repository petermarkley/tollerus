<div
    x-data="{
        msgs: {
            no_cancel: @js(__('tollerus::ui.no_cancel')),
            yes_delete: @js(__('tollerus::ui.yes_delete')),
        },
        tabTarget: $wire.entangle('tabTarget'),
        tabPattern: $wire.entangle('tabPattern'),
        tabNeography: $wire.entangle('tabNeography'),
        ruleForm: $wire.entangle('ruleForm'),
    }"
    @tab-target-switch.window="tabTarget = $event.detail.tabTarget;"
>
    <div id="non-modal-content" class="flex flex-col gap-4">
        <h1 class="font-bold text-2xl px-6 xl:px-0">
            <span>{{ __('tollerus::ui.row_name', ['name' => $rowName])}}</span>
            <span>{{ __('tollerus::ui.auto_inflection') }}</span>
        </h1>
        @if ($row->src_base === null)
            <x-tollerus::alert type="warning">{{ __('tollerus::ui.no_base_row_notice') }}</x-tollerus::alert>
        @endif
        <x-tollerus::panel class="flex flex-col gap-8">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex flex-col gap-2 items-start">
                    <h3 class="font-bold flex flex-row gap-4 items-center text-lg">
                        <x-tollerus::icons.bricks />
                        <span>{{ __('tollerus::ui.base_row') }}</span>
                    </h3>
                    <div>
                        @if ($row->sourceBase)
                            <p class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm p-1">{{ $row->sourceBase->label }}</p>
                        @else
                            <p class="border-zinc-400 text-zinc-700 dark:border-zinc-600 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border rounded-lg shadow-sm p-1 italic text-zinc-500 dark:text-zinc-500">{{ __('tollerus::ui.none') }}</p>
                        @endif
                    </div>
                    <p class="font-normal italic text-zinc-700 dark:text-zinc-500">{{ __('tollerus::ui.base_row_description') }}</p>
                    <div>
                        <a href="{{ route('tollerus.admin.languages.inflection-tables', ['language' => $language->id, 'group' => $group->id]) }}">{{ __('tollerus::ui.edit_at_group_level') }}</a>
                    </div>
                </div>
                <fieldset class="flex flex-col gap-2 items-start">
                    <h3 class="font-bold text-lg">
                        <label for="src_particle" class="flex flex-row gap-4 items-center">
                            <x-tollerus::icons.puzzle />
                            <span>{{ __('tollerus::ui.particle') }}</span>
                        </label>
                    </h3>
                    <x-tollerus::inputs.text-saveable
                        idExpression="'src_particle'"
                        model="ruleForm.row.srcParticle.globalId"
                        modelIsAlpine="true"
                        fieldName="srcParticle"
                    />
                    <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500">{{ __('tollerus::ui.particle_description', ['row' => $row->label]) }}</legend></div>
                </fieldset>
            </div>
            <fieldset class="flex flex-col gap-2 items-start">
                <h3 class="font-bold text-lg">
                    <label for="morph_template" class="flex flex-row gap-4 items-center">
                        <x-tollerus::icons.arrows-merge-right />
                        <span>{{ __('tollerus::ui.morph_template') }}</span>
                    </label>
                </h3>
                <x-tollerus::inputs.text-saveable
                    idExpression="'morph_template'"
                    model="ruleForm.row.morphTemplate"
                    modelIsAlpine="true"
                    fieldName="morphTemplate"
                />
                <x-tollerus::alert type="info" class="whitespace-nowrap">{{ __('tollerus::ui.morph_template_key') }}</x-tollerus::alert>
                <div><legend class="font-normal italic text-zinc-700 dark:text-zinc-500">{{ __('tollerus::ui.morph_template_description') }}</legend></div>
            </fieldset>
        </x-tollerus::panel>
        <h1 class="font-bold text-2xl px-6 xl:px-0">{{ __('tollerus::ui.morph_rules') }}</h1>
        <div>
            <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
                <x-tollerus::inputs.tab
                    switcher="tabTarget"
                    tabName="base"
                    aria-controls="tabpanel-base"
                    title="{{ __('tollerus::ui.base') }}"
                    @click="tabTarget='base'"
                    @keydown.enter.prevent="tabTarget='base'"
                    @keydown.space.prevent="tabTarget='base'"
                >
                    <x-tollerus::icons.bricks class="h-6"/>
                    <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.base') }}</span>
                </x-tollerus::inputs.tab>
                <x-tollerus::inputs.tab
                    switcher="tabTarget"
                    tabName="particle"
                    aria-controls="tabpanel-particle"
                    title="{{ __('tollerus::ui.particle') }}"
                    @click="tabTarget='particle'"
                    @keydown.enter.prevent="tabTarget='particle'"
                    @keydown.space.prevent="tabTarget='particle'"
                >
                    <x-tollerus::icons.puzzle class="h-6"/>
                    <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.particle') }}</span>
                </x-tollerus::inputs.tab>
            </ul>
            @foreach (['base', 'particle'] as $tabTargetName)
                <div
                    id="tabpanel-{{ $tabTargetName }}"
                    role="tabpanel"
                    x-cloak x-show="tabTarget=='{{ $tabTargetName }}'"
                    class="border-4 border-white dark:border-zinc-800 rounded-xl pt-4"
                >
                    <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
                        <x-tollerus::inputs.tab
                            switcher="tabPattern"
                            tabName="transliterated"
                            aria-controls="tabpanel-{{ $tabTargetName }}-transliterated"
                            title="{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}"
                            @click="tabPattern='transliterated'"
                            @keydown.enter.prevent="tabPattern='transliterated'"
                            @keydown.space.prevent="tabPattern='transliterated'"
                        >
                            <x-tollerus::icons.world class="h-6"/>
                            <span class="sr-only md:not-sr-only">{{ mb_ucfirst(config('tollerus.local_transliteration_target', __('tollerus::ui.transliterated'))) }}</span>
                        </x-tollerus::inputs.tab>
                        <x-tollerus::inputs.tab
                            switcher="tabPattern"
                            tabName="phonemic"
                            aria-controls="tabpanel-{{ $tabTargetName }}-phonemic"
                            title="{{ __('tollerus::ui.phonemic') }}"
                            @click="tabPattern='phonemic'"
                            @keydown.enter.prevent="tabPattern='phonemic'"
                            @keydown.space.prevent="tabPattern='phonemic'"
                        >
                            <x-tollerus::icons.speech class="h-6"/>
                            <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.phonemic') }}</span>
                        </x-tollerus::inputs.tab>
                        <x-tollerus::inputs.tab
                            switcher="tabPattern"
                            tabName="native"
                            aria-controls="tabpanel-{{ $tabTargetName }}-native"
                            title="{{ __('tollerus::ui.native') }}"
                            @click="tabPattern='native'"
                            @keydown.enter.prevent="tabPattern='native'"
                            @keydown.space.prevent="tabPattern='native'"
                        >
                            <x-tollerus::icons.neography class="h-6"/>
                            <span class="sr-only md:not-sr-only">{{ __('tollerus::ui.native') }}</span>
                        </x-tollerus::inputs.tab>
                    </ul>
                    @foreach (['transliterated', 'phonemic', 'native'] as $tabPatternName)
                        <x-tollerus::panel
                            id="tabpanel-{{ $tabTargetName }}-{{ $tabPatternName }}"
                            role="tabpanel"
                            x-cloak x-show="tabPattern=='{{ $tabPatternName }}'"
                            class="flex flex-col gap-6"
                        >
                            @php
                                $targetStr = $tabTargetName . '_input';
                                $targetLocal = \PeterMarkley\Tollerus\Enums\MorphRuleTargetType::from($targetStr)->localize();
                                $patternLocal = \PeterMarkley\Tollerus\Enums\MorphRulePatternType::from($tabPatternName)->localize();
                            @endphp
                            <h2 class="font-bold text-xl flex flex-row gap-4 items-baseline">
                                <span>{{ __('tollerus::ui.applied_to_input', ['input' => $targetLocal]) }}</span>
                                <span>&bull;</span>
                                <span>{{ __('tollerus::ui.in_type_representation', ['type' => $patternLocal]) }}</span>
                                <span>&hellip;</span>
                            </h2>
                            @if ($tabPatternName == 'native')
                                <div>
                                    <x-tollerus::inputs.select
                                        id="tab_neography"
                                        label="{{ __('tollerus::ui.neography') }}"
                                        model="tabNeography"
                                    >
                                        @foreach ($language->neographies as $neography)
                                            <option value="{{ (string)$neography->id }}" class="cursor-pointer" x-bind:selected="tabNeography=={{ (string)$neography->id }}">{{ $neography->name }}</option>
                                        @endforeach
                                    </x-tollerus::inputs.select>
                                </div>
                            @endif
                            <p>Lorem ipsum dolor sit amet.</p>
                        </x-tollerus::panel>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>
