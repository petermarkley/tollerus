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
    public bool $dirty = false;

    public function mount(Language $language): void
    {
        $this->language = $language;
        $this->form = $language->getAttributes();
        unset($this->form['id']);
    }

    public function save(): void
    {
        try {
            // Validate
            $this->validate([
                'form.machine_name' => 'alpha_dash:ascii',
            ]);
            // Save to database
            $this->language->fill($this->form);
            $this->language->save();
            // Refresh front-end state
            $this->form = $this->language->getAttributes();
            unset($this->form['id']);
            $this->dispatch('save-success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-failure');
            // Let error keep propagating
            throw $e;
        }
    }

    public function render(): View
    {
        return view('tollerus::livewire.language-editor')
            ->layout('tollerus::components.layout')
            ->title($this->language->name);
    }

    public function openModal(): void
    {
        $this->dispatch('open-modal');
    }
    public function closeModal(): void
    {
        $this->dispatch('close-modal');
    }
}
