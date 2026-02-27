@props([
  'id' => '',
  'label' => '',
  'model' => '',
  'rows' => 10,
  'monospace' => false,
  'wysiwyg' => false,
])
<div
    class="flex flex-col gap-1 items-start"
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        wire:ignore
        data-tollerus-wysiwyg
        x-data="tollerusWysiwyg({
            state: $wire.entangle('{{ $model }}'),
        })"
    @endif
>
    <label for="{{ $id }}">{{ $label }}</label>
    @if (filter_var($wysiwyg, FILTER_VALIDATE_BOOLEAN))
        <div class="w-full flex flex-col items-stretch">
            <div class="w-full p-2 flex flex-row gap-1 justify-between items-center rounded-t-lg border rounded-b border-zinc-400 dark:border-zinc-600">
                <div class="flex flex-row gap-1 items-center">
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('bold')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.bold') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleBold()"
                        >
                            <x-tollerus::icons.micro.bold class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bold') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('bold')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.bold') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleBold()"
                        >
                            <x-tollerus::icons.micro.bold class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bold') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('italic')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.italic') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleItalic()"
                        >
                            <x-tollerus::icons.micro.italic class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.italic') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('italic')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.italic') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleItalic()"
                        >
                            <x-tollerus::icons.micro.italic class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.italic') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('smallcaps')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.smallcaps') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleSmallcaps()"
                        >
                            <x-tollerus::icons.micro.smallcaps class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.smallcaps') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('smallcaps')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.smallcaps') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="toggleSmallcaps()"
                        >
                            <x-tollerus::icons.micro.smallcaps class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.smallcaps') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="true"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.link') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.link class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.link') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="false" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.link') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.link class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.link') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="true"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.bullet_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.list-bullet class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bullet_list') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="false" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.bullet_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.list-bullet class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.bullet_list') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="true"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.numbered_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.list-numbered class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.numbered_list') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="false" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.numbered_list') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.list-numbered class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.numbered_list') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="true"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.conlang_word') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.language class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.conlang_word') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="false" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.conlang_word') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.language class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.conlang_word') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="!isActive('phonemic')"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.phonemic') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="togglePhonemic()"
                        >
                            <x-tollerus::icons.micro.speech class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.phonemic') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="isActive('phonemic')" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.phonemic') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                            @click="togglePhonemic()"
                        >
                            <x-tollerus::icons.micro.speech class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.phonemic') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                    <div>
                        <x-tollerus::inputs.button
                            x-show="true"
                            type="inverse"
                            size="tiny"
                            title="{{ __('tollerus::ui.neography_letters') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.neography class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.neography_letters') }}</span>
                        </x-tollerus::inputs.button>
                        <x-tollerus::inputs.button
                            x-show="false" x-cloak
                            type="inverse-highlight"
                            size="tiny"
                            title="{{ __('tollerus::ui.neography_letters') }}"
                            x-bind:disabled="rawMode"
                            class="relative"
                        >
                            <x-tollerus::icons.micro.neography class="sm:h-6" />
                            <span class="sr-only">{{ __('tollerus::ui.neography_letters') }}</span>
                        </x-tollerus::inputs.button>
                    </div>
                </div>
                <div class="flex flex-row gap-1 items-center">
                    <x-tollerus::inputs.button
                        x-show="!rawMode"
                        type="inverse"
                        size="tiny"
                        title="{{ __('tollerus::ui.edit_as_raw_html') }}"
                        class="relative"
                        @click="rawMode = true"
                    >
                        <x-tollerus::icons.micro.code class="sm:h-6" />
                        <span class="sr-only">{{ __('tollerus::ui.edit_as_raw_html') }}</span>
                    </x-tollerus::inputs.button>
                    <x-tollerus::inputs.button
                        x-show="rawMode" x-cloak
                        type="primary"
                        size="tiny"
                        title="{{ __('tollerus::ui.edit_as_rendered_html') }}"
                        class="relative"
                        @click="rawMode = false"
                    >
                        <x-tollerus::icons.micro.code class="sm:h-6" />
                        <span class="sr-only">{{ __('tollerus::ui.edit_as_rendered_html') }}</span>
                    </x-tollerus::inputs.button>
                </div>
            </div>
            <div
                data-tollerus-wysiwyg-mount
                x-show="!rawMode"
                class="w-full border p-2 w-full rounded-b-lg rounded-t inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 border-zinc-400 dark:border-zinc-600"
            ></div>
            <textarea
                x-show="rawMode" x-cloak
                id="{{ $id }}"
                wire:model.defer="{{ $model }}"
                rows="{{ $rows }}"
                {{ $attributes }}
                class="font-mono border p-2 w-full rounded-b-lg rounded-t inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"
            ></textarea>
        </div>
    @else
        <textarea id="{{ $id }}" wire:model.defer="{{ $model }}" rows="{{ $rows }}" {{ $attributes }} class="@if(filter_var($monospace, FILTER_VALIDATE_BOOLEAN)) font-mono @endif border p-2 w-full rounded-lg inset-shadow-sm bg-zinc-50 dark:bg-zinc-900/30 @error($model) border-red-700 dark:border-red-500 @else border-zinc-400 dark:border-zinc-600 @enderror"></textarea>
        @error($model)
            <p class="text-red-700 dark:text-red-500 text-sm">{{ $message }}</p>
        @enderror
    @endif
</div>
