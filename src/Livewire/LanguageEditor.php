<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\WordClassGroups;

class LanguageEditor extends Component
{
    // Models
    #[Locked] public Language $language;
    #[Locked] public array $neographies = [];
    #[Locked] public array $languageNeographies = [];
    #[Locked] public array $wordClassGroups = [];
    // UI input layer
    public array $infoForm = [];
    public array $neographiesForm = [];
    public array $grammarForm = [];
    // UI display properties
    #[Locked] public array $nativeSpellingCounts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        $folder = __DIR__ . '/../../resources/grammar_presets/';
        $presetFiles = collect(scandir($folder))
            ->map(fn ($f) => $folder . $f)
            ->filter(fn ($path) => (
                is_file($path) &&
                str_contains($path, '.json') &&
                mime_content_type($path) == 'application/json'
            ))->values();
        $presets = $presetFiles
            ->map(fn ($f) => json_decode(file_get_contents($f)))
            ->filter()
            ->mapWithKeys(fn ($f) => [$f->i18n_file => __('tollerus::grammar_presets/' . $f->i18n_file . '.preset_name')])
            ->toArray();
        return view('tollerus::livewire.language-editor', ['presets' => $presets])
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
        $this->wordClassGroups = $language->wordClassGroups->all();
        $this->refreshGrammarForm();
    }

    /**
     * Convenience functions that switch between methods based on tab
     */
    public function refreshForm(string $tab): void
    {
        switch ($tab) {
            case 'info':
                $this->refreshInfoForm();
            break;
            case 'neographies':
                $this->refreshNeographiesForm();
            break;
            case 'grammar':
                $this->refreshGrammarForm();
            break;
        }
    }
    public function save(string $tab, string $afterSuccess = '', array $payload = []): void
    {
        switch ($tab) {
            case 'info':
                $this->infoSave($afterSuccess, $payload);
            break;
            case 'neographies':
                $this->neographiesSave($afterSuccess, $payload);
            break;
            case 'grammar':
                $this->grammarSave($afterSuccess, $payload);
            break;
        }
    }

    /**
     * Tab-specific refresh functions
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
        $this->nativeSpellingCounts = collect($this->neographies)->mapWithKeys(fn ($neography) => [
            $neography->id => NativeSpelling::where('neography_id', $neography->id)
                ->whereHas('form', fn ($q) => $q->where('language_id', $this->language->id))
                ->count()
        ])->toArray();
    }
    public function refreshGrammarForm(): void
    {
        foreach ($this->wordClassGroups as $group) {
            $group->loadMissing([
                'wordClasses',
                'features.featureValues',
                'inflectionTables.filterValues',
                'inflectionTables.rows.filterValues',
            ]);
        }
        $this->grammarForm = collect($this->wordClassGroups)
            ->mapWithKeys(function ($group) {return [
                $group->id => [
                    'primaryClass' => $group->primary_class,
                    'classes' => $group->wordClasses->mapWithKeys(fn ($class) => [
                        $class->id => [
                            'name' => $class->name,
                            'name_brief' => $class->name_brief,
                        ],
                    ])->toArray(),
                    'features' => null, // FIXME
                    'tables' => null, // FIXME
                ]
            ];})->toArray();
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
                    NativeSpelling::where('neography_id', $pivotRow->neography_id)
                        ->whereHas('form', fn ($q) => $q->where('language_id', $this->language->id))
                        ->delete();
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
            $this->languageNeographies = $this->language->neographies->all();
            $this->refreshNeographiesForm();
            $this->dispatch('save-neographies-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-neographies-failure');
            // Let error keep propagating
            throw $e;
        }
    }
    public function grammarSave(): void
    {
        //
    }
}
