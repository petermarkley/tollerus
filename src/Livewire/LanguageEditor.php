<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;

class LanguageEditor extends Component
{
    #[Locked] public Language $language;
    #[Locked] public array $neographies = [];
    #[Locked] public array $languageNeographies = [];
    public array $infoForm = [];
    public array $neographiesForm = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.language-editor')
            ->layout('tollerus::components.layout')
            ->title($this->language->name);
    }
    public function mount(Language $language): void
    {
        $this->language = $language;
        $this->refreshInfoForm();
        $this->neographies = Neography::orderBy('machine_name')->get()->all();
        $this->languageNeographies = $language->neographies->all();
        $this->refreshNeographiesForm();
    }

    /**
     * Front-end refresh functions, per tab
     */
    public function refreshInfoForm(): void
    {
        $this->infoForm = $this->language->toArray();
        unset($this->infoForm['id']);
        unset($this->infoForm['primary_neography']);
    }
    public function refreshNeographiesForm(): void
    {
        $this->neographiesForm = collect($this->neographies)->mapWithKeys(fn ($neography) => [
            $neography->id => collect($this->languageNeographies)
                ->pluck('id')
                ->contains($neography->id)
        ])->toArray();
        $this->neographiesForm['primary_neography'] = $this->language->primary_neography;
    }

    /**
     * Convenience function that switches between save methods based on tab
     */
    public function save(string $tab, string $afterSuccess = '', array $payload = []): void
    {
        switch ($tab) {
            case 'info':
                $this->infoSave($afterSuccess, $payload);
            break;
            case 'neographies':
                $this->neographiesSave($afterSuccess, $payload);
            break;
        }
    }

    /**
     * Tab-specific save functions
     */
    public function infoSave(string $afterSuccess = '', array $payload = []): void
    {
        try {
            // Validate
            $this->validate([
                'infoForm.machine_name' => 'alpha_dash:ascii',
            ]);
            // Save to database
            $this->language->fill($this->infoForm);
            $this->language->save();
            // Refresh front-end state
            $this->refreshInfoForm();
            $this->dispatch('save-info-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-info-failure');
            // Let error keep propagating
            throw $e;
        }
    }
    public function neographiesSave(string $afterSuccess = '', array $payload = []): void
    {
        try {
            // Prepare
            $activated = collect($this->neographies)
                ->pluck('id')
                ->filter(fn ($id) => $this->neographiesForm[$id]);
            // Validate
            $this->validate([
                'neographiesForm.primary_neography' => ['nullable', Rule::in($activated->toArray())],
            ]);
            // Sync with database
            $pivotRows = LanguageNeography::where('language_id', $this->language->id)->get();
            foreach ($pivotRows as $pivotRow) {
                if (!($activated->contains($pivotRow->neography_id))) {
                    // This neography was deactivated by the user
                    $pivotRow->delete();
                    // FIXME delete NativeSpelling rows?
                }
            }
            foreach ($activated as $activeNeographyId) {
                if (!($pivotRows->pluck('neography_id')->contains($activeNeographyId))) {
                    // This neography was activated by the user
                    (new LanguageNeography([
                        'neography_id' => $activeNeographyId,
                        'language_id' => $this->language->id,
                    ]))->save();
                }
            }
            $this->language->primary_neography = $this->neographiesForm['primary_neography'];
            $this->language->save();
            // Refresh front-end state
            $this->refreshNeographiesForm();
            $this->dispatch('save-neographies-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-neographies-failure');
            // Let error keep propagating
            throw $e;
        }
    }
}
