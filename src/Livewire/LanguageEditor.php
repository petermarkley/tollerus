<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Language;

class LanguageEditor extends Component
{
    public Language $language;

    public function mount(Language $language): void
    {
        $this->language = $language;
    }

    public function render(): View
    {
        return view('tollerus::livewire.language-editor')
            ->layout('tollerus::components.layout')
            ->title($this->language->name);
    }
}
