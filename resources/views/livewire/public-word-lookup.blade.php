<div
    x-data="{ highlight: $wire.entangle('highlight') }"
    class="mx-auto mt-4 w-full xl:px-25 flex flex-col gap-4 items-start"
>
    @if($languages->count() == 0)
        <div class="w-full flex flex-col gap-4 items-center">
            <p class="text-center text-tollerus-text">{{ __('tollerus::ui.no_data_notice') }}</p>
            <a href="{{ route('tollerus.admin.index') }}" class="px-4 py-2 rounded-lg shadow font-bold bg-tollerus-primary hover:bg-tollerus-primary-hover text-tollerus-text-inverse">{{ __('tollerus::ui.admin_area') }}</a>
        </div>
    @else
        <x-tollerus::public.nav-main currentPage="word_lookup" :langCount="$languages->count()"/>
        <div class="w-full flex flex-col gap-2 bg-tollerus-surface rounded-[32px] shadow-lg p-2 text-tollerus-text">
            <form
                wire:submit="search"
                class="w-full rounded-full flex flex-row gap-1 items-stretch"
            >
                <div class="relative flex justify-center items-center">
                    <label for="search_type" class="sr-only">{{ __('tollerus::ui.search_type') }}</label>
                    <select
                        id="search_type"
                        wire:model="type"
                        title="{{ __('tollerus::ui.search_type') }}"
                        class="bg-tollerus-surface hover:bg-tollerus-surface-hover cursor-pointer py-2 px-4 h-11 flex justify-center items-center appearance-none rounded-l-[22px] rounded-r-lg pr-6 font-bold border-2 border-tollerus-border"
                    >
                        @foreach (\PeterMarkley\Tollerus\Enums\SearchType::cases() as $thisSearchType)
                            <option value="{{ $thisSearchType->value }}">{{ mb_ucfirst($thisSearchType->localize()) }}</option>
                        @endforeach
                    </select>
                    <x-tollerus::icons.triangle class="absolute pointer-events-none right-2 top-1/2 scale-[80%] rotate-90 -translate-y-1/2" />
                </div>
                <div class="flex-grow flex justify-center items-center">
                    <div class="relative flex flex-col gap-1 items-start flex-grow">
                        <label for="search_string" class="sr-only">{{ __('tollerus::ui.search_term') }}</label>
                        <input
                            type="text"
                            id="search_string"
                            wire:model.defer="key"
                            class="appearance-none w-full border p-2 w-full rounded-lg inset-shadow-sm bg-tollerus-muted border-tollerus-border/50"
                            placeholder="{{ __('tollerus::ui.search_for_entry') }}"
                        />
                    </div>
                </div>
                <div class="shrink-0 flex justify-center items-center">
                    <button
                        type="submit"
                        title="{{ __('tollerus::ui.submit_search') }}"
                        class="relative w-10 h-10 mr-[0.125rem] rounded-l-full rounded-r-full flex justify-center items-center cursor-pointer bg-tollerus-secondary hover:bg-tollerus-secondary-hover text-tollerus-text-inverse"
                    >
                        <x-tollerus::icons.magnifying-glass class="w-7 h-7"/>
                        <span class="sr-only">{{ __('tollerus::ui.submit_search') }}</span>
                    </button>
                </div>
            </form>
            <div class="flex flex-col xl:flex-row gap-2 items-stretch">
                <div class="relative xl:w-80 shrink-0">
                    <div class="w-full h-60 xl:h-auto min-h-60 xl:absolute xl:inset-y-0 overflow-y-scroll overflow-x-hidden flex flex-col gap-2 justify-start items-stretch rounded-lg xl:rounded-bl-[22px] inset-shadow-sm bg-tollerus-muted border-2 border-tollerus-border/50">
                        @foreach ($results as $result)
                            @php($selected = $selectedResult === $result['global_id'])
                            <button
                                data-entry-id="{{ $result['entryGlobalId'] }}"
                                data-form-id="{{ $result['global_id'] }}"
                                @class([
                                    'py-1 px-4 flex flex-row gap-2 justify-start items-center font-bold cursor-pointer',
                                    'hover:bg-tollerus-surface/50 text-tollerus-secondary hover:text-tollerus-secondary-hover' => !$selected && !($result['irregular']),
                                    'hover:bg-tollerus-surface/50 text-tollerus-text-irregular' => !$selected && $result['irregular'],
                                    'bg-tollerus-secondary text-tollerus-text-inverse hover:bg-tollerus-secondary-hover' => $selected,
                                ])
                                wire:click="selectResult($el.dataset.formId)"
                            >
                                <span class="font-bold whitespace-nowrap">{{ $result['transliterated'] }}</span>
                                <span class="whitespace-nowrap tollerus_custom_{{ $result['primaryNeographyMachineName'] }}">{{ $result['native'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div
                    @if (isset($primaryNeography) && $primaryNeography !== null)
                        x-data="{ currentNeography: {{ $primaryNeography->id }} }"
                    @endif
                    class="w-full xl:w-[calc(100%-20.5rem)] flex-grow min-h-60 p-8 flex flex-col gap-6 rounded-lg rounded-b-[22px] xl:rounded-bl-lg inset-shadow-sm bg-tollerus-muted border-2 border-tollerus-border/50"
                >
                    @if ($entry !== null)
                        <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div @class(['md:col-span-2'=>!$multipleNeographies])>
                                <span>{{ __('tollerus::ui.language') }}:</span>
                                <a
                                    href="{{ route('tollerus.public.languages.show', ['language' => $language]) }}"
                                    class="text-tollerus-primary hover:text-tollerus-primary-hover"
                                >{{ $language->name }}</a>
                            </div>
                            @if ($multipleNeographies)
                                <div class="flex">
                                    <div class="relative flex flex-row gap-2 items-center">
                                        <label for="writing_system">{{ __('tollerus::ui.writing_system') }}</label>
                                        <select
                                            id="writing_system"
                                            x-model="currentNeography"
                                            class="appearance-none pr-6 bg-tollerus-surface hover:bg-tollerus-surface-hover border-2 border-tollerus-bg hover:border-tollerus-bg/50 cursor-pointer rounded-lg py-2 px-4 h-11 flex justify-center items-center shadow-lg"
                                        >
                                            @foreach ($languageNeographies as $neography)
                                                <option value="{{ $neography->id }}">{{ $neography->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-tollerus::icons.micro.chevron-down class="absolute pointer-events-none right-1 top-1/2 -translate-y-1/2" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-lg flex flex-row gap-12 justify-start items-center">
                            <a
                                id="{{ $entry->global_id }}"
                                class="relative flex flex-row flex-wrap sm:flex-nowrap gap-y-1 gap-x-8 items-center justify-start text-tollerus-text"
                                wire:click="selectResult('{{ $entry->primaryForm->global_id }}')"
                            >
                                <span class="font-bold whitespace-nowrap">{{ $primaryForm->transliterated }}</span>
                                <span class="italic whitespace-nowrap">/{{ $primaryForm->phonemic }}/</span>
                                @foreach ($languageNeographies as $neography)
                                    @php($nativeSpelling = $primaryForm->nativeSpellings->firstWhere('neography_id', $neography->id))
                                    <span
                                        x-show="currentNeography=={{ $neography->id }}" x-cloak
                                        class="whitespace-nowrap tollerus_custom_{{ $neography->machine_name }}"
                                    >
                                        @if ($nativeSpelling)
                                            {{ $nativeSpelling->spelling }}
                                        @else
                                            &ndash;&nbsp;&ndash;&nbsp;&ndash;
                                        @endif
                                    </span>
                                @endforeach
                                <x-tollerus::public.highlight :globalId="$entry->primaryForm->global_id"/>
                            </a>
                            <a
                                href="{{ route('tollerus.public.index', ['id' => $entry->global_id]) }}"
                                class="relative text-tollerus-secondary hover:text-tollerus-secondary-hover"
                                title="{{ __('tollerus::ui.canonical_url') }}"
                            >
                                <x-tollerus::icons.link />
                                <span class="sr-only">{{ __('tollerus::ui.canonical_url') }}</span>
                            </a>
                        </h3>
                        <div class="flex flex-col gap-6">
                            @foreach ($lexemes as $lexeme)
                                <div class="flex flex-col gap-4 items-start">
                                    <a
                                        id="{{ $lexeme['model']->global_id }}"
                                        class="relative"
                                        wire:click="selectResult($el.id)"
                                    >
                                        <span class="text-tollerus-text font-mono font-bold opacity-50 tracking-widest">{{ $lexeme['class']->name }}</span>
                                        <x-tollerus::public.highlight :globalId="$lexeme['model']->global_id"/>
                                    </a>
                                    @if ($lexeme['tables']->count() > 0)
                                        @if ($lexeme['collapse'])
                                            <x-tollerus::drawer open="false" rootClass="w-full" class="flex flex-col gap-4 w-full" isPublic="true">
                                                <x-slot:heading-button>
                                                    <div class="flex flex-row gap-2 px-2 py-1 justify-start items-center rounded-t-xl rounded-bl bg-tollerus-secondary group-has-hover:bg-tollerus-secondary-hover text-tollerus-text-inverse">
                                                        <span>{{ __('tollerus::ui.inflections') }}</span>
                                                    </div>
                                                </x-slot:heading-button>
                                                <x-slot:heading>
                                                    <div class="flex-grow border-b-2 border-tollerus-secondary"></div>
                                                </x-slot:heading>
                                                @include('tollerus::livewire.public-word-lookup._inflection-tables')
                                            </x-tollerus::drawer>
                                        @else
                                            @include('tollerus::livewire.public-word-lookup._inflection-tables')
                                        @endif
                                    @endif
                                    <ol class="w-full pl-4 sm:pl-10 list-decimal flex flex-col gap-2">
                                        @foreach ($lexeme['model']->senses->sortBy('num') as $sense)
                                            <li class="space-y-2">
                                                <p>
                                                    @if(!empty($sense->usage))
                                                        <span class="p-1 rounded bg-tollerus-surface text-sm" style="font-variant: small-caps;">{{ $sense->usage }}</span>
                                                    @endif
                                                    @tollerusBodyText($sense->body)
                                                </p>
                                                @if ($sense->subsenses->count() > 0)
                                                    <ul class="pl-6 list-disc flex flex-col gap-2">
                                                        @foreach ($sense->subsenses->sortBy('num') as $subsense)
                                                            <li>
                                                                <p>
                                                                    @if(!empty($subsense->usage))
                                                                        <span class="p-1 rounded bg-tollerus-surface text-sm" style="font-variant: small-caps;">{{ $subsense->usage }}</span>
                                                                    @endif
                                                                    @tollerusBodyText($subsense->body)
                                                                </p>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </div>
                        @if ($entry->etym)
                            <div>
                                <p>
                                    <span>{{ __('tollerus::ui.origin') }}:</span>
                                    @tollerusBodyText($entry->etym)
                                </p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
