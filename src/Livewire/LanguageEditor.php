<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Enums\SearchType;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\Pivots\LanguageNeography;
use PeterMarkley\Tollerus\Domain\Language\Actions\LoadGrammarPreset;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class LanguageEditor extends Component
{
    use WithPagination, WithoutUrlPagination;
    use HasModelCache;
    private $cacheRoot = 'wordClassGroups';
    public string $tab = 'info';
    // Models
    #[Locked] public Language $language;
    #[Locked] public array $neographies = [];
    #[Locked] public array $languageNeographies = [];
    #[Locked] public array $wordClassGroups = [];
    // UI input layer
    public array $infoForm = [];
    public array $neographiesForm = [];
    public array $grammarForm = [];
    public string $sortBy = 'transliterated';
    public string $searchStr = '';
    public string $searchType = 'transliterated';
    // UI display properties
    #[Locked] public array $nativeSpellingCounts = [];
    #[Locked] public array $presetData = [];
    #[Locked] public array $presetSelectOpts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        $neographyId = $this->language->primaryNeography?->id;
        /**
         * It's best if we do all of our data prep and sorting
         * in the database here, because we're paginating the
         * result.
         */
        $hasEntries = $this->language->entries()->exists();
        $entriesQuery = $this->language->entries()
            ->leftJoin('forms as pf', 'pf.id', '=', 'entries.primary_form')
            ->leftJoin('native_spellings as ns', function ($join) use ($neographyId) {
                $join->on('ns.form_id', '=', 'pf.id');
                $join->where('ns.neography_id', '=', $neographyId ?? -1);
            })->select([
                'entries.*',
                'pf.transliterated as transliterated',
                'ns.spelling as native',
                'ns.sort_key as sort_key',
            ]);
        // Set the sort method
        switch ($this->sortBy) {
            case 'transliterated':
                $entriesQuery->orderBy('pf.transliterated');
            break;
            case 'native':
                $entriesQuery->orderBy('ns.sort_key');
            break;
        }
        // Search filter
        if (!empty($this->searchStr)) {
            switch (SearchType::from($this->searchType)) {
                case SearchType::Transliterated:
                    $entriesQuery->where('pf.transliterated', 'like', '%'.$this->searchStr.'%');
                break;
                case SearchType::Native:
                    $entriesQuery->where('ns.spelling', 'like', '%'.$this->searchStr.'%');
                break;
                case SearchType::Definition:
                    $like = '%'.$this->searchStr.'%';
                    $entriesQuery->where(function ($q) use ($like) {
                        $q->whereExists(function ($sq) use ($like) {
                            $sq->selectRaw('1')
                                ->from('lexemes')
                                ->join('senses', 'senses.lexeme_id', '=', 'lexemes.id')
                                ->whereColumn('lexemes.entry_id', 'entries.id')
                                ->where('senses.body', 'like', $like);
                        })->orWhereExists(function ($sq) use ($like) {
                            $sq->selectRaw('1')
                                ->from('lexemes')
                                ->join('senses', 'senses.lexeme_id', '=', 'lexemes.id')
                                ->join('subsenses', 'subsenses.sense_id', '=', 'senses.id')
                                ->whereColumn('lexemes.entry_id', 'entries.id')
                                ->where('subsenses.body', 'like', $like);
                        });
                    });
                break;
            }
        }
        // Run the query
        $paginator = $entriesQuery->paginate(48);
        return view('tollerus::livewire.language-editor', [
                'paginator' => $paginator,
                'hasEntries' => $hasEntries,
            ])->layout('tollerus::components.layouts.admin', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                ],
            ])->title($this->language->name);
    }
    public function mount(Language $language, ?string $tab = null): void
    {
        $this->language = $language;
        $this->language->loadMissing(['primaryNeography']);
        $this->tab = $tab ?? 'info';

        // Info tab
        $this->refreshInfoForm();
        $this->neographies = Neography::orderBy('machine_name')->get()->all();
        $this->languageNeographies = $language->neographies->all();

        // Neographies tab
        $this->refreshNeographiesForm();

        // Grammar tab
        $this->refreshGrammarForm();
        $folder = __DIR__ . '/../../resources/data/grammar_presets/';
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

    public function search()
    {
        $this->resetPage();
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
        }
    }
    public function setSortBy(string $sortBy): void
    {
        if (in_array($sortBy, ['transliterated', 'native'])) {
            $this->sortBy = $sortBy;
            $this->resetPage();
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
                'inflectionTables.columns.filterValues',
                'inflectionTables.columns.rows.filterValues',
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
                    'features' => $group->features->mapWithKeys(fn ($feature) => [
                        $feature->id => [
                            'name' => $feature->name,
                            'nameBrief' => $feature->name_brief,
                            'featureValues' => $feature->featureValues->mapWithKeys(fn ($value) => [
                                $value->id => [
                                    'name' => $value->name,
                                    'nameBrief' => $value->name_brief,
                                ],
                            ])->toArray(),
                        ],
                    ])->toArray(),
                    'tables' => $group->inflectionTables->sortBy('position')->map(fn ($table) => [
                        'tableId' => $table->id,
                        'position' => $table->position,
                        'columns' => $table->columns->sortBy('position')->map(fn ($column) => [
                            'columnId' => $column->id,
                            'label' => $column->label,
                            'position' => $column->position,
                            'rows' => $column->rows->sortBy('position')->map(fn ($row) => [
                                'rowId' => $row->id,
                                'label' => $row->label,
                                'labelBrief' => $row->label_brief,
                                'labelLong' => $row->label_long,
                                'position' => $row->position,
                            ])->values()->toArray(),
                        ])->values()->toArray(),
                    ])->values()->toArray(),
                    'inflectionsUrl' => route('tollerus.admin.languages.inflections.edit', [
                        'language' => $this->language,
                        'wordClassGroup' => $group,
                    ]),
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
                'infoForm.name' => [
                    Rule::unique('PeterMarkley\Tollerus\Models\Language', 'name')->ignore($this->language->id),
                ],
                'infoForm.machine_name' => [
                    'alpha_dash:ascii',
                    Rule::unique('PeterMarkley\Tollerus\Models\Language', 'machine_name')->ignore($this->language->id),
                ],
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

    /**
     * Granular CRUD-type functions
     */
    public function createGroup(): void
    {
        $connection = config('tollerus.connection', 'tollerus');
        DB::connection($connection)->transaction(function () {
            $group = $this->language->wordClassGroups()->create();
            $this->createWordClass($group, true);
        });
    }
    public function updateGroupPrimaryClass(string $groupId): void
    {
        $groupModel = $this->findInCache('grammar-group-update-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
            ],
        ]);
        $groupModel->primary_class = $this->grammarForm[$groupId]['primaryClass'];
        $groupModel->save();
        $this->refreshGrammarForm();
    }
    public function deleteGroup(string $groupId): void
    {
        WordClassGroup::findOrFail((int)$groupId)->delete();
        $this->refreshGrammarForm();
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function createWordClass(string|WordClassGroup $group, bool $setAsPrimary = false): void
    {
        /**
         * $group can be either a string or a model instance. If it's a string,
         * then we need to convert it into a model instance.
         */
        if (is_string($group) == 'string') {
            $groupModel = collect($this->wordClassGroups)->firstWhere('id', (int)$group);
        } else {
            $groupModel = $group;
        }
        // Should be a model instance by now, no matter what.
        if (!($groupModel instanceof WordClassGroup)) {
            $this->dispatch('grammar-class-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['groupId' => [__('tollerus::error.invalid_word_class_group')]]);
            return;
        }
        // Create the model
        try {
            $class = CreateWithUniqueName::handle(
                startNum: $this->language->wordClasses()->count(),
                createFunc: fn ($tryName) => $groupModel->wordClasses()->create([
                    'language_id' => $this->language->id,
                    'name' => $tryName,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('grammar-class-add-failure');
            throw $e;
        }
        if ($setAsPrimary) {
            $groupModel->primary_class = $class->id;
            $groupModel->save();
        }
        $this->refreshGrammarForm();
    }
    public function updateClass(string $groupId, string $classId, string $propName, string $propVal, ?string $domId = ''): void
    {
        $classModel = $this->findInCache('text-save-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
                'relation' => 'wordClasses',
            ],
            [
                'id' => $classId,
                'objectType' => WordClass::class,
                'failMessage' => ['classId' => [__('tollerus::error.invalid_word_class')]],
            ],
        ], $domId);
        if ($propName === 'name' || $propName === 'name_brief') {
            try {
                $classModel[$propName] = $propVal;
                $classModel->save();
                $this->refreshGrammarForm();
                $this->dispatch('text-save-success', id: $domId);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $error = match ($propName) {
                    'name' => ['wordClass.name' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                    'name_brief' => ['wordClass.nameBrief' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                };
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages($error);
            }
        }
    }
    public function deleteWordClass(string $wordClassId): void
    {
        WordClass::findOrFail((int)$wordClassId)->delete();
        $this->refreshGrammarForm();
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function createFeature(string $groupId): void
    {
        $groupModel = $this->findInCache('grammar-feature-add-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
            ],
        ]);
        try {
            $feature = CreateWithUniqueName::handle(
                startNum: $groupModel->features()->count(),
                createFunc: fn ($tryName) => $groupModel->features()->create([
                    'name' => $tryName,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('grammar-feature-add-failure');
            throw $e;
        }
        $this->refreshGrammarForm();
    }
    public function updateFeature(string $groupId, string $featureId, string $propName, string $propVal, ?string $domId = ''): void
    {
        $featureModel = $this->findInCache('text-save-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
                'relation' => 'features',
            ],
            [
                'id' => $featureId,
                'objectType' => Feature::class,
                'failMessage' => ['featureId' => [__('tollerus::error.invalid_feature')]],
            ],
        ], $domId);
        if ($propName === 'name' || $propName === 'name_brief') {
            try {
                $featureModel[$propName] = $propVal;
                $featureModel->save();
                $this->refreshGrammarForm();
                $this->dispatch('text-save-success', id: $domId);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $error = match ($propName) {
                    'name' => ['feature.name' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                    'name_brief' => ['feature.nameBrief' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                };
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages($error);
            }
        }
    }
    public function deleteFeature(string $featureId): void
    {
        Feature::findOrFail((int)$featureId)->delete();
        $this->refreshGrammarForm();
    }
    public function createFeatureValue(string $groupId, string $featureId): void
    {
        $featureModel = $this->findInCache('grammar-value-add-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
                'relation' => 'features',
            ],
            [
                'id' => $featureId,
                'objectType' => Feature::class,
                'failMessage' => ['featureId' => [__('tollerus::error.invalid_feature')]],
            ],
        ]);
        try {
            $featureValue = CreateWithUniqueName::handle(
                startNum: $featureModel->featureValues()->count(),
                createFunc: fn ($tryName) => $featureModel->featureValues()->create([
                    'name' => $tryName,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('grammar-value-add-failure');
            throw $e;
        }
        $this->refreshGrammarForm();
    }
    public function updateFeatureValue(string $groupId, string $featureId, string $featureValueId, string $propName, string $propVal, ?string $domId = ''): void
    {
        $featureValueModel = $this->findInCache('text-save-failure', [
            [
                'id' => $groupId,
                'objectType' => WordClassGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_word_class_group')]],
                'relation' => 'features',
            ],
            [
                'id' => $featureId,
                'objectType' => Feature::class,
                'failMessage' => ['featureId' => [__('tollerus::error.invalid_feature')]],
                'relation' => 'featureValues',
            ],
            [
                'id' => $featureValueId,
                'objectType' => FeatureValue::class,
                'failMessage' => ['featureValueId' => [__('tollerus::error.invalid_feature_value')]],
            ],
        ], $domId);
        if ($propName === 'name' || $propName === 'name_brief') {
            try {
                $featureValueModel[$propName] = $propVal;
                $featureValueModel->save();
                $this->refreshGrammarForm();
                $this->dispatch('text-save-success', id: $domId);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $error = match ($propName) {
                    'name' => ['featureValue.name' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                    'name_brief' => ['featureValue.nameBrief' => [__('tollerus::error.duplicate_of_unique_per_group')]],
                };
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages($error);
            }
        }
    }
    public function deleteFeatureValue(string $featureValueId): void
    {
        FeatureValue::findOrFail((int)$featureValueId)->delete();
        $this->refreshGrammarForm();
    }

    /**
     * Public utility functions
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
