<div class="mx-auto mt-4 w-full xl:px-25 flex flex-col gap-4 items-start">
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
                        wire:model="searchType"
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
                            wire:model.defer="searchStr"
                            class="appearance-none w-full border p-2 w-full rounded-lg inset-shadow-sm bg-tollerus-muted border-tollerus-border/50"
                            placeholder="{{ __('tollerus::ui.search_for_entry') }}"
                        />
                    </div>
                </div>
                <div class="shrink-0 flex justify-center items-center">
                    <button
                        type="secondary"
                        size="small"
                        htmlType="submit"
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
                    <div class="w-full h-60 xl:h-auto min-h-60 xl:absolute xl:inset-y-0 overflow-y-scroll rounded-lg xl:rounded-bl-[22px] inset-shadow-sm bg-tollerus-muted border-2 border-tollerus-border/50">
                        @foreach ($results as $result)
                            <pre class="text-xs">{!! json_encode($result, JSON_PRETTY_PRINT) !!}</pre>
                        @endforeach
                    </div>
                </div>
                <div class="flex-grow min-h-30 p-8 flex flex-col gap-6 rounded-lg rounded-b-[22px] xl:rounded-bl-lg inset-shadow-sm bg-tollerus-muted border-2 border-tollerus-border/50">
                    @if ($entry !== null)
                        <div>
                            <span>{{ __('tollerus::ui.language') }}:</span>
                            <a
                                href="{{ route('tollerus.public.languages.show', ['language' => $language]) }}"
                                class="text-tollerus-primary hover:text-tollerus-primary-hover"
                            >{{ $language->name }}</a>
                        </div>
                        <h3 class="text-2xl flex flex-row gap-12 justify-start items-center">
                            <a id="{{ $entry->global_id }}" class="flex flex-row gap-8 items-center justify-start text-tollerus-text">
                                <span class="font-bold">{{ $primaryForm->transliterated }}</span>
                                <span class="italic">/{{ $primaryForm->phonemic }}/</span>
                                <span class="tollerus_{{ $primaryNeography->machine_name }}">{{ $primaryNativeSpelling->spelling }}</span>
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
                                <div class="flex flex-col gap-4">
                                    <a
                                        id="{{ $lexeme['model']->global_id }}"
                                        class="text-tollerus-text font-mono font-bold opacity-50 tracking-widest"
                                    >{{ $lexeme['class']->name }}</a>
                                    @if ($lexeme['tables']->count() > 0)
                                        <div class="flex flex-row flex-wrap gap-4 items-start">
                                            @foreach ($lexeme['tables'] as $table)
                                                <table class="border border-tollerus-border">
                                                    <thead>
                                                        <tr>
                                                            <td></td>
                                                            <th scope="col" class="p-1">{{ $table['model']->label }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($table['rows'] as $row)
                                                            <tr>
                                                                <th scope="row" class="p-1 text-right">{{ $row['model']->label }}</th>
                                                                <td class="p-1">
                                                                    <a
                                                                        id="{{ $row['form']->global_id }}"
                                                                        @class([
                                                                            'flex flex-row gap-2',
                                                                            'text-tollerus-text' => !($row['form']->irregular),
                                                                            'text-tollerus-text-irregular' => $row['form']->irregular,
                                                                        ])
                                                                    >
                                                                        <span>{{ $row['form']->transliterated }}</span>
                                                                        <span class="italic">/{{ $row['form']->phonemic }}/</span>
                                                                        <span class="tollerus_{{ $primaryNeography->machine_name }}">{{ $row['formNative']->spelling }}</span>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endforeach
                                        </div>
                                    @endif
                                    <ol class="pl-10 list-decimal flex flex-col gap-2">
                                        @foreach ($lexeme['model']->senses->sortBy('num') as $sense)
                                            <li class="space-y-2">
                                                {!! $sense->body !!}
                                                @if ($sense->subsenses->count() > 0)
                                                    <ul class="pl-6 list-disc flex flex-col gap-2">
                                                        @foreach ($sense->subsenses->sortBy('num') as $subsense)
                                                            <li>{!! $subsense->body !!}</li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endforeach
                        </div>
                        <div>
                            <p>
                                <span>{{ __('tollerus::ui.origin') }}:</span>
                                {!! $entry->etym !!}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
