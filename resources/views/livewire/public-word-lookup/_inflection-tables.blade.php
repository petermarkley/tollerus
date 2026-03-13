<div
    class="w-full overflow-x-scroll"
    style="scrollbar-color: rgb(var(--tollerus-text)) rgb(var(--tollerus-surface));"
>
    <div class="w-fit xl:w-max p-2 flex flex-col gap-4 items-start xl:items-center text-sm bg-tollerus-bg/30 rounded-lg">
        @foreach ($lexeme['tables'] as $table)
            <div class="flex flex-row flex-wrap xl:flex-nowrap gap-x-4 gap-y-6 items-start justify-start xl:justify-center">
                @foreach ($table['columns'] as $columnIndex => $column)
                    <table class="w-max">
                        @if ($column['model']->show_label)
                            <thead
                                @class([
                                    'xl:hidden' => $table['model']->cols_fold,
                                ])
                            >
                                <tr @class(['xl:hidden'=>$table['model']->align_on_stack])>
                                    <th scope="col" colspan="2" class="px-1 font-normal text-center whitespace-nowrap">{{ $column['model']->label }}</th>
                                </tr>
                                @if ($table['model']->align_on_stack)
                                    <tr class="hidden xl:table-row">
                                        <td
                                            @class([
                                                'xl:hidden' => $columnIndex!=0 && $table['model']->rows_fold,
                                            ])
                                        ></td>
                                        <th scope="col" class="px-1 font-normal text-left whitespace-nowrap">{{ $column['model']->label }}</th>
                                    </tr>
                                @endif
                            </thead>
                        @endif
                        <tbody>
                            @foreach ($column['rows'] as $row)
                                <tr>
                                    <th
                                        scope="row"
                                        @class([
                                            'px-4 text-right font-normal',
                                            'xl:hidden' => $columnIndex!=0 && $table['model']->rows_fold,
                                        ])
                                    >
                                        @if ($row['model']->show_label)
                                            <span class="whitespace-nowrap inline sm:hidden">
                                                @if (empty($row['model']->label_brief))
                                                    {{ $row['model']->label }}
                                                @else
                                                    <abbr
                                                        title="{{ $row['model']->label }}"
                                                        class="no-underline"
                                                    >{{ $row['labelBrief'] }}</abbr>
                                                @endif
                                            </span>
                                            <span class="whitespace-nowrap hidden sm:inline xl:hidden">{{ $row['label'] }}</span>
                                            <span class="whitespace-nowrap hidden xl:inline">{{ $row['labelLong'] }}</span>
                                        @endif
                                    </th>
                                    <td class="px-1">
                                        @if ($row['form'] !== null)
                                            <a
                                                id="{{ $row['form']->global_id }}"
                                                @class([
                                                    'relative grid grid-cols-3 gap-2',
                                                    'text-tollerus-text' => !($row['form']->irregular),
                                                    'text-tollerus-text-irregular' => $row['form']->irregular,
                                                ])
                                                wire:click="selectResult($el.id)"
                                            >
                                                <span class="font-bold whitespace-nowrap">{{ $row['form']->transliterated }}</span>
                                                <span class="italic whitespace-nowrap">/{{ $row['form']->phonemic }}/</span>
                                                @foreach ($languageNeographies as $neography)
                                                    @php($nativeSpelling = $row['form']->nativeSpellings->firstWhere('neography_id', $neography->id))
                                                    <span
                                                        x-show="currentNeography=={{ $neography->id }}" x-cloak
                                                        class="whitespace-nowrap tollerus_custom_{{ $neography->machine_name }}"
                                                    >
                                                        @if ($nativeSpelling)
                                                            {{ $row['formNative']->spelling }}
                                                        @else
                                                            &ndash;&nbsp;&ndash;&nbsp;&ndash;
                                                        @endif
                                                    </span>
                                                @endforeach
                                                <x-tollerus::public.highlight :globalId="$row['form']->global_id"/>
                                            </a>
                                        @else
                                            <span>&ndash;&nbsp;&ndash;&nbsp;&ndash;</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
