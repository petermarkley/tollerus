<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\View\View;

class Modal extends Component
{
    public bool $open;
    public string $message;

    #[On('open-modal')]
    public function open(): void { $this->open = true; }

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
