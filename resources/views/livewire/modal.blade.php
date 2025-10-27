<div id="modal" x-cloak x-data wire:show="open" class="w-[100vw] h-[100vh] bg-black/40 backdrop-blur-sm z-100 absolute inset-0 flex justify-center items-center">
    <x-tollerus::panel class="flex flex-col gap-4 w-[500px]">
        <div class="w-full">
            {{ $message }}
        </div>
        <div class="w-full flex flex-row justify-start gap-2">
            @foreach ($buttons as $button)
                @if (isset($button['payload']))
                    <x-tollerus::inputs.button :type="$button['type']" x-on:click="$dispatch('{{ $button['clickEvent'] }}', {{ json_encode($button['payload']) }})">{{ $button['text'] }}</x-tollerus::inputs.button>
                @else
                    <x-tollerus::inputs.button :type="$button['type']" wire:click="$dispatch('{{ $button['clickEvent'] }}')">{{ $button['text'] }}</x-tollerus::inputs.button>
                @endif
            @endforeach
        </div>
    </x-tollerus::panel>
    <div x-data @close-modal.window="$store.scrollLock.unlock()" class="w-0 h-0"></div>
</div>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('scrollLock', {
        y: 0,
        lock() {
            this.y = window.scrollY;
            document.body.style.top = `-${this.y}px`;
            document.body.style.position = 'fixed';
            document.getElementById('modal').style.top = `${this.y}px`;
        },
        unlock() {
            document.body.style.position = '';
            document.body.style.top='';
            document.getElementById('modal').style.top = '';
            window.scrollTo(0, this.y);
        },
    });
});
</script>
@endpush
@endonce
