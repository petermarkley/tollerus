<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\View\View;

class Modal extends Component
{
    public bool $open;
    public string $message;
    public array $buttons;

    #[On('open-modal')]
    public function open(string $message, array $buttons): void {
        $this->message = $message;
        $this->buttons = $buttons;
        $this->open = true;
    }

    #[On('close-modal')]
    public function close(): void { $this->open = false; }

    public function mount(): void
    {
        $this->open = false;
        $this->message = '';
    }

    public function render(): View
    {
        return view('tollerus::livewire.modal');
    }
}
