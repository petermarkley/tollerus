<div
    id="modal"
    x-data x-cloak
    x-show="$store.modal.open"
    class="w-[100vw] h-[100vh] bg-black/40 backdrop-blur-sm z-100 absolute inset-0 flex justify-center items-center"
    @open-modal.window="$store.scrollLock.lock(); $store.modal.show($event.detail.message, $event.detail.buttons)"
    @close-modal.window="$store.modal.close(); $store.scrollLock.unlock()"
>
    <x-tollerus::panel class="flex flex-col gap-4 w-[500px]">
        <div class="w-full" x-text="$store.modal.message"></div>
        <div class="w-full flex flex-row justify-start gap-2">
            <template x-for="btn in $store.modal.buttons">
                <div>
                    <template x-if="btn.type == 'primary'">
                        <x-tollerus::inputs.button
                            type="primary"
                            @click="$dispatch(btn.clickEvent, btn.payload ?? {}); $dispatch('close-modal');"
                            x-text="btn.text"
                        ></x-tollerus::inputs.button>
                    </template>
                    <template x-if="btn.type == 'secondary'">
                        <x-tollerus::inputs.button
                            type="secondary"
                            @click="$dispatch(btn.clickEvent, btn.payload ?? {}); $dispatch('close-modal');"
                            x-text="btn.text"
                        ></x-tollerus::inputs.button>
                    </template>
                </div>
            </template>
        </div>
    </x-tollerus::panel>
    <div x-data @close-modal.window="$store.scrollLock.unlock()" class="w-0 h-0"></div>
</div>
@once
@push('tollerus-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('modal', {
        open: false,
        message: '',
        buttons: [],
        show(message, buttons) {
            this.message = message;
            this.buttons = buttons || [];
            this.open = true;
        },
        close() {
            this.open = false;
            this.message = '';
            this.buttons = [];
        },
    });
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
