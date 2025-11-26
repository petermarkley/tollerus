<div
    x-data="{
        tabTarget: $wire.entangle('tabTarget'),
        tabPattern: $wire.entangle('tabPattern'),
    }"
    @tab-target-switch.window="tabTarget = $event.detail.tabTarget;"
>
    <div id="non-modal-content">
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">
            <span>{{ __('tollerus::ui.row_name', ['name' => $rowName])}}</span>
            <span>{{ __('tollerus::ui.auto_inflection') }}</span>
        </h1>
        <x-tollerus::panel class="mb-4">
            Lorem ipsum dolor sit amet.
        </x-tollerus::panel>
        <ul class="px-4 flex flex-row gap-4 justify-start items-end" role="tablist">
            <x-tollerus::inputs.tab
                switcher="tabTarget"
                tabName="base"
                aria-controls="tabpanel-base"
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
                        <h2 class="font-bold text-xl">{{ mb_ucfirst($targetLocal) }} {{ $patternLocal }}</h2>
                        <p>Lorem ipsum dolor sit amet.</p>
                    </x-tollerus::panel>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
