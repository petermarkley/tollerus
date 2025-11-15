@props([
	'open' => 'true',
	'rootClass' => '',
])
<div class="{{ (empty($rootClass) ? '' : $rootClass) }}" x-id="['drawer']" x-data="{ drawerOpen: {{ $open }} }">
	<h2 class="font-bold text-xl flex flex-row items-end w-full mb-4">
		<button
			x-bind:aria-controls="$id('drawer')"
			x-bind:aria-expanded="drawerOpen"
			type="button"
			@click="drawerOpen = !drawerOpen;"
			class="flex flex-row gap-2 justify-start items-center cursor-pointer group"
		>
			<div
				x-bind:class="{ 'rotate-90': drawerOpen }"
				class="transition-transform text-zinc-600 dark:text-zinc-400 group-has-hover:text-zinc-500 group-has-hover:dark:text-zinc-300 font-bold cursor-pointer disabled:cursor-not-allowed disabled:font-normal disabled:text-zinc-300 disabled:dark:text-zinc-600"
			>
				<x-tollerus::icons.triangle/>
			</div>
			<div x-bind:id="$id('drawer-heading')" class="w-full">
				{{ $headingButton }}
			</div>
		</button>
		{{ $heading }}
	</h2>
	<div class="overflow-hidden">
		<div
			x-bind:id="$id('drawer')"
			role="region"
			x-bind:aria-labelledby="$id('drawer-heading')"
			x-bind:aria-hidden="!drawerOpen"
			x-bind:inert="!drawerOpen"
			{{ $attributes->merge(['class' => (empty($contentClass) ? '' : $contentClass)]) }} x-bind:class="{
				'drawer-content': true,
				'drawer-content-closed': !drawerOpen
			}"
		>
			{{ $slot }}
		</div>
	</div>
</div>
