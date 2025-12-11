@use('PeterMarkley\Tollerus\Enums\WritingDirection')
<x-tollerus::layout :breadcrumbs="$breadcrumbs">
    <div
        id="non-modal-content"
        x-data="{
            msgs: {
                no_cancel: @js(__('tollerus::ui.no_cancel')),
                yes_delete: @js(__('tollerus::ui.yes_delete')),
                delete_neography_confirmation: @js( $deleteMsgs ),
            }
        }"
        @neography-delete.window="$store.neographies.delete($event.detail.url);"
    >
        <x-slot name="title">{{ __('tollerus::ui.neographies') }}</x-slot>
        <h1 class="font-bold text-2xl mb-4 px-6 xl:px-0">{{ __('tollerus::ui.neographies') }}</h1>
        <div class="flex flex-col gap-4 items-stretch">
            @foreach ($neographies as $neography)
                <x-tollerus::panel class="flex flex-col gap-2">
                    <h2 class="flex flex-row gap-2 items-center justify-between">
                        <a
                            class="text-zinc-900 dark:text-zinc-300 font-bold text-xl flex flex-row gap-2 items-center"
                            title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                            href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography]) }}"
                        >
                            <x-tollerus::icons.neography class="h-8"/>
                            <span>{{ $neography->name }}</span>
                        </a>
                        <div class="flex flex-row gap-2 items-center">
                            <x-tollerus::button
                                type="secondary"
                                size="small"
                                title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                                href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography]) }}"
                            >
                                <x-tollerus::icons.edit class="h-6 w-6"/>
                                <span class="sr-only">{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}</span>
                            </x-tollerus::button>
                            <x-tollerus::inputs.button
                                type="secondary"
                                size="small"
                                title="{{ __('tollerus::ui.delete_thing', ['thing' => $neography->name]) }}"
                                @click="$dispatch('open-modal', {message: msgs['delete_neography_confirmation']['{{ $neography->machine_name }}'], buttons: [
                                    {text: msgs['no_cancel'], type: 'secondary', clickEvent: 'close-modal'},
                                    {text: msgs['yes_delete'], type: 'primary', clickEvent: 'neography-delete', payload: {url: '{{ route('tollerus.admin.neographies.destroy', ['neography' => $neography]) }}'} },
                                ]});"
                            >
                                <x-tollerus::icons.delete/>
                                <span class="sr-only">{{ __('tollerus::ui.delete_thing', ['thing' => $neography->name]) }}</span>
                            </x-tollerus::inputs.button>
                        </div>
                    </h2>
                    <div class="flex flex-row justify-start gap-4">
                        @php
                            switch ($neography->direction_primary) {
                                case WritingDirection::LeftToRight:
                                    $flexStr = 'flex-row';
                                    $flexStr .= match ($neography->direction_secondary) {
                                        WritingDirection::TopToBottom => ' flex-wrap',
                                        WritingDirection::BottomToTop => ' flex-wrap-reverse',
                                    };
                                break;
                                case WritingDirection::RightToLeft:
                                    $flexStr = 'flex-row-reverse';
                                    $flexStr .= match ($neography->direction_secondary) {
                                        WritingDirection::TopToBottom => ' flex-wrap',
                                        WritingDirection::BottomToTop => ' flex-wrap-reverse',
                                    };
                                break;
                                case WritingDirection::TopToBottom:
                                    $flexStr = 'flex-col';
                                    $flexStr .= match ($neography->direction_secondary) {
                                        WritingDirection::LeftToRight => ' flex-wrap',
                                        WritingDirection::RightToLeft => ' flex-wrap-reverse',
                                    };
                                break;
                                case WritingDirection::BottomToTop:
                                    $flexStr = 'flex-col-reverse';
                                    $flexStr .= match ($neography->direction_secondary) {
                                        WritingDirection::LeftToRight => ' flex-wrap',
                                        WritingDirection::RightToLeft => ' flex-wrap-reverse',
                                    };
                                break;
                            }
                        @endphp
                        @if (count($glyphPreview[$neography->machine_name]) > 0)
                            <x-tollerus::pane
                                class="w-full max-h-28 overflow-hidden"
                                role="img"
                                aria-label="{{ __('tollerus::ui.glyphs') }}"
                                href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography]) }}"
                                title="{{ __('tollerus::ui.edit_thing', ['thing' => $neography->name]) }}"
                            >
                                <div class="w-full max-h-28 flex {{ $flexStr }} justify-start items-baseline gap-2 mask-b-to-85%">
                                    @foreach ($glyphPreview[$neography->machine_name] as $glyph)
                                        {{-- Controller generates these with classes: 'h-12 w-auto' --}}
                                        {!! $glyph['svg'] !!}
                                    @endforeach
                                </div>
                            </x-tollerus::pane>
                        @else
                            <x-tollerus::missing-data href="{{ route('tollerus.admin.neographies.edit', ['neography' => $neography]) }}">{{ __('tollerus::ui.no_glyphs') }}</x-tollerus::missing-data>
                        @endif
                    </div>
                </x-tollerus::panel>
            @endforeach
            <x-tollerus::inputs.missing-data
                size="medium"
                title="{{ __('tollerus::ui.add_neography') }}"
                class="relative flex flex-row gap-2 justify-center items-center w-full"
                @click="$store.neographies.create();"
            >
                <x-tollerus::icons.plus/>
                <span class="sr-only lg:not-sr-only">{{ __('tollerus::ui.add_neography') }}</span>
            </x-tollerus::inputs.missing-data>
        </div>
    </div>
    <x-tollerus::modal/>
    @once
    @push('tollerus-scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('neographies', {
            create() {
                fetch('{{ route('tollerus.admin.neographies.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                }).then(response => response.json())
                .then(data => {
                    if (data.id) {
                        window.location.href = '{{ route('tollerus.admin.neographies.edit', '#') }}'.replaceAll('#', data.id);
                    }
                }).catch(error => console.error('Network error:', error));
            },
            delete(url) {
                fetch(url, {
                    method: 'DELETE',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                }).then(response => {
                    if (response.ok) {
                        window.location.reload();
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
</x-tollerus::layout>
