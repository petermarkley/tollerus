@props([
	'open' => 'true',
	'rootClass' => '',
])
<div class="{{ (empty($rootClass) ? '' : $rootClass) }}" x-id="['drawer']" x-data="{ drawerOpen: {{ $open }} }">
	<div class="flex flex-row gap-2 justify-start items-center mb-4">
		<x-tollerus::inputs.button
			x-bind:aria-controls="$id('drawer')"
			x-bind:aria-expanded="drawerOpen"
			type="inverse"
			x-bind:class="{ 'rotate-90': drawerOpen }"
			@click="drawerOpen = !drawerOpen;"
			class="transition-transform"
		>
			<x-tollerus::icons.triangle/>
		</x-tollerus::inputs.button>
		<div x-bind:id="$id('drawer-heading')" class="w-full">
			{{ $heading }}
		</div>
	</div>
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
