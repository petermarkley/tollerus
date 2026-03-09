<x-tollerus::layouts.admin>
    <x-slot name="title">{{ __('tollerus::ui.tollerus_admin_area') }}</x-slot>
    <div class="flex flex-col gap-8">
        <div class="px-6 xl:px-0 flex flex-row justify-center items-center">
            <x-tollerus::logo.color-icon class="w-full max-w-90 h-auto block dark:hidden text-zinc-700"/>
            <x-tollerus::logo.color-icon light class="w-full max-w-90 h-auto hidden dark:block"/>
        </div>
        <div class="px-6 xl:px-0 text-xl flex flex-col gap-4 items-center">
            <p class="text-center">{{ __('tollerus::ui.tollerus_welcome') }}<br>{{ __('tollerus::ui.tollerus_description') }}</p>
            <ul class="list-disc text-left pl-8 flex flex-col gap-2">
                <li><del class="text-zinc-700 dark:text-zinc-400">{{ __('tollerus::ui.how_to_use') }}</del> <span class="text-base italic">{{ __('tollerus::ui.coming_soon') }}</span></li>
                <li><a href="https://github.com/petermarkley/tollerus">{{ __('tollerus::ui.tollerus_on_github') }}</a></li>
                <li><a href="https://petermarkley.com/">{{ __('tollerus::ui.peter_markleys_portfolio') }}</a></li>
            </ul>
        </div>
        <div class="px-6 xl:px-0 text-xl flex flex-col gap-4 items-center">
            {!! Str::markdown(__('tollerus::ui.donate_request', ['donate_url' => 'https://paypal.me/petermarkley'])) !!}
        </div>
        <div class="px-6 xl:px-0 flex flex-col gap-4 items-center mt-4">
            <div class="flex flex-row gap-4 justify-center items-center">
                <div class="relative">
                    <a
                        href="{{ route('tollerus.admin.neographies.index') }}"
                        title="{{ __('tollerus::ui.neographies') }}"
                        class="group text-zinc-900 dark:text-zinc-300 hover:text-zinc-900 hover:dark:text-zinc-300"
                    >
                        <x-tollerus::panel class="relative flex flex-row gap-4 justify-center items-center group-has-hover:bg-zinc-100 group-has-hover:dark:bg-zinc-700">
                            <x-tollerus::icons.neography class="w-12 h-12"/>
                            <span class="text-3xl sr-only sm:not-sr-only">{{ __('tollerus::ui.neographies') }}</span>
                            @if ($neographyCount > 0)
                                <span class="block text-2xl font-bold text-white dark:text-zinc-900 bg-zinc-600 dark:bg-zinc-300 rounded-full w-8 h-8 flex justify-center items-center text-center">{{ $neographyCount }}</span>
                            @endif
                        </x-tollerus::panel>
                    </a>
                    @if (!$hasData)
                        <div class="absolute left-[50%] transform-[translateX(-50%)] h-10 -top-12 z-10 pointer-events-none flex justify-center items-center text-center bg-zinc-800 dark:bg-white text-zinc-300 dark:text-zinc-900 rounded-lg shadow py-2 px-4">
                            <svg viewBox="0 0 20 10" class="absolute w-8 h-4 -bottom-4 left-[50%] transform-[translateX(-50%)] text-zinc-800 dark:text-white">
                                <path d="M 20,0 L 10,10 L 0,0 z" fill="currentColor" />
                            </svg>
                            <span class="font-bold whitespace-nowrap">{{ __('tollerus::ui.start_here') }}</span>
                        </div>
                    @endif
                </div>
                <div>
                    <a
                        href="{{ route('tollerus.admin.languages.index') }}"
                        title="{{ __('tollerus::ui.languages') }}"
                        class="group text-zinc-900 dark:text-zinc-300 hover:text-zinc-900 hover:dark:text-zinc-300"
                    >
                        <x-tollerus::panel class="relative flex flex-row gap-4 justify-center items-center group-has-hover:bg-zinc-100 group-has-hover:dark:bg-zinc-700">
                            <x-tollerus::icons.language class="w-12 h-12"/>
                            <span class="text-3xl sr-only sm:not-sr-only">{{ __('tollerus::ui.languages') }}</span>
                            @if ($languageCount > 0)
                                <span class="block text-2xl font-bold text-white dark:text-zinc-900 bg-zinc-600 dark:bg-zinc-300 rounded-full w-8 h-8 flex justify-center items-center text-center">{{ $languageCount }}</span>
                            @endif
                        </x-tollerus::panel>
                    </a>
                </div>
            </div>
            <div class="flex flex-row gap-4 justify-center items-center">
                <div>
                    <a
                        href="{{ route('tollerus.public.index') }}"
                        title="{{ __('tollerus::ui.browse_dictionary') }}"
                        class="group text-zinc-900 dark:text-zinc-300 hover:text-zinc-900 hover:dark:text-zinc-300"
                    >
                        <x-tollerus::panel class="relative flex flex-row gap-4 justify-center items-center group-has-hover:bg-zinc-100 group-has-hover:dark:bg-zinc-700">
                            <x-tollerus::icons.eye class="w-12 h-12"/>
                            <span class="text-3xl sr-only sm:not-sr-only">{{ __('tollerus::ui.browse_dictionary') }}</span>
                        </x-tollerus::panel>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-tollerus::layouts.admin>
