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
use PeterMarkley\Tollerus\Models\WordClass;
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
        $this->wordClassGroups = $this->language->wordClassGroups->all();
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
     * Granular CRUD-type functions
     */
    public function createGroup(): void
    {
        $group = $this->language->wordClassGroups()->create();
        $this->createWordClass($group, true);
    }
    public function updateGroupPrimaryClass(string $groupId): void
    {
        $groupModel = collect($this->wordClassGroups)->firstWhere('id', (int)$groupId);
        if (!($groupModel instanceof WordClassGroup)) {
            $this->dispatch('grammar-group-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['preset' => [__('tollerus::error.invalid_word_class_group')]]);
            return;
        }
        $groupModel->primary_class = $this->grammarForm[$groupId]['primaryClass'];
        $groupModel->save();
        $this->refreshGrammarForm();
    }
    public function deleteGroup(string $groupId): void
    {
        WordClassGroup::findOrFail((int)$groupId)->delete();
        $this->refreshGrammarForm();
    }
    public function createWordClass(string|WordClassGroup $group, bool $setAsPrimary = false): void
    {
        /**
         * $group can be either a string or a model instance. If it's a string,
         * then we need to convert it into a model instance.
         */
        if (gettype($group) == 'string') {
            $groupModel = collect($this->wordClassGroups)->firstWhere('id', (int)$group);
        } else {
            $groupModel = $group;
        }
        // Should be a model instance by now, no matter what.
        if (!($groupModel instanceof WordClassGroup)) {
            $this->dispatch('grammar-class-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['preset' => [__('tollerus::error.invalid_word_class_group')]]);
            return;
        }
        /**
         * This DB table has a non-nullable 'name' field with a unique constraint.
         * Since we're not prompting the user for a name first, that means we
         * need a placeholder name that's unique or else the insert will fail.
         */
        $class = null;
        $num = $this->language->wordClasses()->count();
        $base = __('tollerus::ui.untitled');
        $maxAttempts = 20;
        for ($i=0; $i < $maxAttempts; $i++) {
            $tryNum = $num + $i;
            $tryName = $i==0 ? $base : $base . " ({$tryNum})";
            try {
                $class = $groupModel->wordClasses()->create([
                    'language_id' => $this->language->id,
                    'name' => $tryName,
                ]);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                /**
                 * If this isn't a `unique` constraint violation, then
                 * something else is wrong and we need to surface the error.
                 */
                $sqlState = $e->getCode();
                $driverCode = $e->errorInfo[1] ?? null;
                if (!($sqlState === '23000' && $driverCode === 1062)) {
                    throw $e;
                    return;
                }
            }
        }
        if ($class === null || !($class instanceof WordClass)) {
            throw new \RuntimeException(__('tollerus::error.max_attempts_adding_word_class'));
            return;
        }
        if ($setAsPrimary) {
            $groupModel->primary_class = $class->id;
            $groupModel->save();
        }
        $this->refreshGrammarForm();
    }
    public function updateClass(string $groupId, string $classId, string $propName, string $propVal): void
    {
        /**
         * We could just directly query for word class like:
         *
         *    WordClass::where('group_id', $groupId)
         *      ->where('id', $classId)
         *      ->first()
         *
         * However, this method uses cached data and might
         * actually save us a trip to the DB.
         */
        $groupModel = collect($this->wordClassGroups)->firstWhere('id', (int)$groupId);
        if (!($groupModel instanceof WordClassGroup)) {
            $this->dispatch('grammar-group-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['preset' => [__('tollerus::error.invalid_word_class_group')]]);
            return;
        }
        $classModel = $groupModel->wordClasses->firstWhere('id', (int)$groupId);
        if (!($classModel instanceof WordClass)) {
            $this->dispatch('grammar-group-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['preset' => [__('tollerus::error.invalid_word_class_group')]]);
            return;
        }
        if ($propName === 'name' || $propName === 'name_brief') {
            $classModel[$propName] = $propVal;
            $classModel->save();
            $this->refreshGrammarForm();
        }
    }
    public function deleteWordClass(string $wordClassId): void
    {
        WordClass::findOrFail((int)$wordClassId)->delete();
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
            $this->refreshGrammarForm();
            $this->dispatch('preset-button-success');
        } catch (\Throwable $e) {
            $this->dispatch('preset-button-failure');
            return;
        }
    }
}
