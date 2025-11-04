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
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Domain\Language\Actions\LoadGrammarPreset;

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
    #[Locked] public array $presetData = [];
    #[Locked] public array $presetSelectOpts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.language-editor', ['presetSelectOpts' => $this->presetSelectOpts])
            ->layout('tollerus::components.layout')
            ->title($this->language->name);
    }
    public function mount(Language $language): void
    {
        $this->language = $language;

        // Info tab
        $this->refreshInfoForm();
        $this->neographies = Neography::orderBy('machine_name')->get()->all();
        $this->languageNeographies = $language->neographies->all();

        // Neographies tab
        $this->refreshNeographiesForm();
        $this->wordClassGroups = $language->wordClassGroups->all();

        // Grammar tab
        $this->refreshGrammarForm();
        $folder = __DIR__ . '/../../resources/grammar_presets/';
        $presetFiles = collect(scandir($folder))
            ->map(fn ($f) => $folder . $f)
            ->filter(fn ($path) => (
                is_file($path) &&
                str_contains($path, '.json') &&
                mime_content_type($path) == 'application/json'
            ))->values();
        $this->presetData = $presetFiles
            ->map(fn ($f) => json_decode(file_get_contents($f)))
            ->filter()
            ->mapWithKeys(function ($file) {
                $i18n_prefix = 'tollerus::grammar_presets/' . $file->i18n_file;
                $featureKeys = collect($file->groups)
                    ->filter(fn ($g) => isset($g->features))
                    ->pluck('features')
                    ->flatten(1)
                    ->pluck('i18n_key')
                    ->unique()
                    ->values();
                return [$file->i18n_file => [
                    'name' => __($i18n_prefix . '.preset_name'),
                    'previewHeading' => __('tollerus::ui.preview_of_thing', ['thing' => __($i18n_prefix . '.preset_name')]),
                    'groups' => collect($file->groups)
                        ->map(function ($group) use ($i18n_prefix) {
                            $i18n_key = collect($group->classes)
                                ->filter(fn ($c) => $c->primary)
                                ->first()->i18n_key;
                            return [
                                'name' => __($i18n_prefix . '.word_classes.' . $i18n_key . '.name'),
                                'featureNum' => (isset($group->features) ? count($group->features) : 0),
                            ];
                        })->toArray(),
                    'features' => $featureKeys->map(fn ($k) => __($i18n_prefix . '.' . $k . '._name'))->toArray(),
                ]];
            })->toArray();
        $this->presetSelectOpts = collect($this->presetData)
            ->mapWithKeys(fn ($p, $k) => [$k => $p['name']])
            ->toArray();
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
                $this->wordClassGroups = $this->language->wordClassGroups->all();
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
                            'nameBrief' => $class->name_brief,
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

    /**
     * Granular create & delete functions
     */
    public function deleteGroup(string $groupId): void
    {
        WordClassGroup::findOrFail((int)$groupId)->delete();
        $this->refreshGrammarForm();
    }

    /**
     * Utility functions
     */
    public function loadGrammarPreset($preset): void
    {
        if (!(collect($this->presetData)->keys()->contains($preset))) {
            $this->dispatch('preset-button-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['preset' => [__('tollerus::error.invalid_preset')]]);
            return;
        }
        $loadAction = new LoadGrammarPreset;
        try {
            $loadAction($this->language, $preset);
            $this->wordClassGroups = $this->language->wordClassGroups->all();
            $this->refreshGrammarForm();
            $this->dispatch('preset-button-success');
        } catch (\Throwable $e) {
            $this->dispatch('preset-button-failure');
            return;
        }
    }
}
