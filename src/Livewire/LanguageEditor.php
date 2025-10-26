<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Language;

class LanguageEditor extends Component
{
    #[Locked] public Language $language;
    public array $form = [];

    public function mount(Language $language): void
    {
        $this->language = $language;
        $this->form = $language->getAttributes();
        unset($this->form['id']);
    }

    public function save(): void
    {
        $this->language->fill($this->form);
        $this->language->save();
    }

    public function render(): View
    {
        return view('tollerus::livewire.language-editor')
            ->layout('tollerus::components.layout')
            ->title($this->language->name);
    }
}
