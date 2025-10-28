@props([
    'switcher' => 'tab',
    'tabName' => '',
])
<li
    role="tab"
    x-bind:aria-selected="{{ $switcher }} == '{{ $tabName }}'"
    tabindex="0"
    x-bind:class="{
        'rounded-t-lg flex flex-row justify-start items-center gap-2 cursor-pointer py-2 px-4 flex focus:outline-2 outline-offset-2 outline-blue-700 dark:outline-white': true,
        'bg-zinc-50 dark:bg-zinc-900 hover:bg-white hover:dark:bg-zinc-800': {{ $switcher }}!='{{ $tabName }}',
        'bg-white dark:bg-zinc-800 hover:bg-zinc-50 hover:dark:bg-zinc-700': {{ $switcher }}=='{{ $tabName }}'
    }"
    {{ $attributes }}
>
    {{ $slot }}
</li>
