<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\Pivots\FormFeatureValue;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class EntryEditor extends Component
{
    use HasModelCache;
    private $cacheRoot = 'lexemes';
    // Models
    #[Locked] public Language $language;
    #[Locked] public Entry $entry;
    #[Locked] public array $lexemes;
    // UI input layer
    public array $infoForm = [];
    // UI display properties
    #[Locked] public array $wordClassGroups = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        if ($this->entry->primaryForm === null) {
            $entryName = __('tollerus::ui.entry_nameless');
        } else {
            $entryName = $this->entry->primaryForm->transliterated;
        }
        $pageTitle = mb_ucfirst($entryName);
        $neographyId = $this->language->primaryNeography?->id;
        return view('tollerus::livewire.entry-editor', [
                'entryName' => $entryName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'entries',
                    ]), 'text' => $this->language->name],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, Entry $entry): void
    {
        $this->entry = $entry;
        $this->language = $language;

        $this->refreshForm();
    }

    /**
     * UI functions
     */
    public function refreshForm(): void
    {
        $this->language->loadMissing([
            'neographies',
            'wordClasses',
        ]);
        $neographies = $this->language->neographies;
        $this->entry->load([
            'lexemes.wordClass',
            'lexemes.forms.inflectionValues.feature',
            'lexemes.forms.nativeSpellings',
            'lexemes.senses.subsenses',
        ]);
        $this->lexemes = $this->entry->lexemes->sortBy('position')->all();
        $this->infoForm = [
            'primaryForm' => $this->entry->primary_form,
            'etym' => $this->entry->etym,
            'lexemes' => collect($this->lexemes)->mapWithKeys(function ($lexeme) use ($neographies) {
                return [$lexeme->id => [
                    'globalId' => $lexeme->global_id,
                    'wordClassId' => $lexeme->wordClass->id,
                    'wordClassName' => $lexeme->wordClass->name,
                    'wordClassGroupId' => $lexeme->wordClass->group_id,
                    'position' => $lexeme->position,
                    'forms' => $lexeme->forms->mapWithKeys(function ($form) use ($neographies) {
                        return [$form->id => [
                            'globalId' => $form->global_id,
                            'transliterated' => $form->transliterated,
                            'phonemic' => $form->phonemic,
                            'irregular' => (bool)($form->irregular),
                            'nativeSpellings' => $neographies->sortBy('machine_name')->map(function ($n) use ($form) {
                                $nativeSpelling = $form->nativeSpellings->firstWhere('neography_id', $n->id);
                                return [
                                    'nativeSpellingId' => ($nativeSpelling===null? null : $nativeSpelling->id),
                                    'neographyId' => $n->id,
                                    'neographyName' => $n->name,
                                    'neographyMachineName' => $n->machine_name,
                                    'spelling' => ($nativeSpelling===null? null : $nativeSpelling->spelling),
                                ];
                            })->toArray(),
                            'inflectionValues' => $form->inflectionValues->mapWithKeys(function ($value) {
                                return [$value->id => [
                                    'featureId'   => $value->feature->id,
                                    'featureName' => $value->feature->name,
                                    'valueId'     => $value->id,
                                    'valueName'   => $value->name,
                                ]];
                            })->toArray(),
                        ]];
                    })->toArray(),
                    'senses' => $lexeme->senses->mapWithKeys(function ($sense) {
                        return [$sense->id => [
                            'num' => $sense->num,
                            'body' => $sense->body,
                            'subsenses' => $sense->subsenses->mapWithKeys(function ($subsense) {
                                return [$subsense->id => [
                                    'num' => $subsense->num,
                                    'body' => $subsense->body,
                                ]];
                            })->toArray(),
                        ]];
                    })->toArray(),
                ]];
            })->toArray(),
        ];
        $this->language->loadMissing([
            'wordClassGroups.wordClasses',
            'wordClassGroups.primaryClass',
            'wordClassGroups.features.featureValues',
        ]);
        $this->wordClassGroups = $this->language->wordClassGroups->sortBy('id')->map(function ($group) {
            if ($group->primaryClass === null) {
                $groupName = __('tollerus::ui.group_nameless');
            } else {
                $groupName = $group->primaryClass->name;
            }
            return [
                'id' => $group->id,
                'name' => $groupName,
                'classes' => $group->wordClasses->sortBy('id')->map(fn ($class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                ])->toArray(),
                'features' => $group->features->sortBy('name')->map(fn ($feature) => [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'values' => $feature->featureValues->sortBy('name')->map(fn ($value) => [
                        'id' => $value->id,
                        'name' => $value->name
                    ])->toArray(),
                ])->toArray(),
            ];
        })->toArray();
    }
    public function infoSave(): void
    {
        try {
            $this->entry->etym = $this->infoForm['etym'];
            $this->entry->save();
            // Refresh front-end state
            $this->refreshForm();
            $this->dispatch('save-info-success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-info-failure');
            // Let error keep propagating
            throw $e;
        }
    }

    /**
     * Granular CRUD-type functions
     */
    public function updatePrimaryForm(string $primaryFormId): void
    {
        if (!empty($primaryFormId)) {
            // Find model
            $formModel = collect($this->lexemes)->flatMap->forms->firstWhere('id', $primaryFormId);
            if (!($formModel instanceof Form)) {
                $this->dispatch('primaryform-update-failure');
                throw \Illuminate\Validation\ValidationException::withMessages(['primaryFormId' => [__('tollerus::error.invalid_form')]]);
            }
        }
        // Update entry
        $this->entry->primary_form = (isset($formModel) ? $formModel->id : null);
        $this->entry->save();
        $this->refreshForm();
    }
    public function createLexeme(string $wordClassId): void
    {
        // Make sure the word class exists on this language
        $wordClassModel = $this->language->wordClasses->firstWhere('id', $wordClassId);
        if (!($wordClassModel instanceof WordClass)) {
            $this->dispatch('lexeme-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['wordClassId' => [__('tollerus::error.invalid_word_class')]]);
        }
        // Make sure it doesn't already exist on this entry
        /**
         * This is not enforced at the database level, because what if
         * at some future point we want to change the UI and allow
         * multiple lexemes of the same word class on one entry? It's
         * not completely implausible. This softer decision seems best
         * to enforce right here at the PHP level.
         */
        if (collect($this->lexemes)->pluck('wordClass')->contains($wordClassModel)) {
            $this->dispatch('lexeme-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['wordClassId' => [__('tollerus::error.dupliacte_of_unique_per_entry')]]);
        }

        // Create lexeme
        $nextPosition = collect($this->lexemes)->max('position') + 1;
        $lexeme = $this->entry->lexemes()->create([
            'language_id' => $this->language->id,
            'word_class_id' => $wordClassModel->id,
            'position' => $nextPosition,
        ]);

        // Create forms if appropriate
        $wordClassModel->loadMissing([
            'group.inflectionTables.filterValues',
            'group.inflectionTables.rows.filterValues',
        ]);
        $alreadyHadForm = collect($this->lexemes)
            ->reduce(fn ($c, $l) => $c || $l->forms()->exists(), false);
        $alreadyHadFormInGroup = collect($this->lexemes)
            ->filter(fn ($l) => $l->wordClass->group_id == $wordClassModel->group_id)
            ->reduce(fn ($c, $l) => $c || $l->forms()->exists(), false);
        $addedForm = false;
        if (!$alreadyHadFormInGroup) {
            /**
             * For inflected word classes, we want to scaffold a set of word forms
             * based on any inflection tables that are currently configured.
             *
             * One form for each inflection row, with all the relevant filters
             * added as inflection values.
             */
            foreach ($wordClassModel->group->inflectionTables->sortBy('position') as $table) {
                foreach ($table->rows->sortBy('position') as $row) {
                    // Create the form
                    $form = $lexeme->forms()->create([
                        'language_id' => $this->language->id,
                    ]);
                    $addedForm = true;
                    // Maybe set as primary
                    if ($row->src_base === null && $this->entry->primary_form === null) {
                        $this->entry->primary_form = $form->id;
                        $this->entry->save();
                    }
                    // Add filters from the table
                    foreach ($table->filterValues as $value) {
                        (new FormFeatureValue([
                            'form_id' => $form->id,
                            'feature_id' => $value->feature_id,
                            'value_id' => $value->id,
                        ]))->save();
                    }
                    // Add filters from the row
                    foreach ($row->filterValues as $value) {
                        (new FormFeatureValue([
                            'form_id' => $form->id,
                            'feature_id' => $value->feature_id,
                            'value_id' => $value->id,
                        ]))->save();
                    }
                }
            }
        }
        /**
         * Now, if it's not an inflected word class and no other forms exist,
         * we want to add one and set it to primary.
         */
        if (!$alreadyHadForm && !$addedForm) {
            $form = $lexeme->forms()->create([
                'language_id' => $this->language->id,
            ]);
            $this->entry->primary_form = $form->id;
            $this->entry->save();
        }

        $this->refreshForm();
    }
    public function deleteLexeme(string $lexemeId): void
    {
        Lexeme::findOrFail((int)$lexemeId)->delete();
        $this->refreshForm();
    }
    public function swapLexemes(string $lexemeId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($lexemeId, $neighborId) {
                $lexemesCollection = collect($this->lexemes);
                $lexemeModel   = $lexemesCollection->firstWhere('id', $lexemeId);
                $neighborModel = $lexemesCollection->firstWhere('id', $neighborId);
                $oldLexemePosition   = (int) $this->infoForm['lexemes'][$lexemeId]['position'];
                $oldNeighborPosition = (int) $this->infoForm['lexemes'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $lexemesCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $lexemeModel->position = $oldNeighborPosition;
                $lexemeModel->save();
                $neighborModel->position = $oldLexemePosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('lexeme-swap-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function createForm(string $lexemeId): void
    {
        // Find model
        $lexemeModel = $this->findInCache('form-add-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
            ],
        ]);
        // Create form
        $lexemeModel->forms()->create([
            'language_id' => $this->language->id,
        ]);
        $this->refreshForm();
    }
    public function updateForm(string $lexemeId, string $formId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $formModel = $this->findInCache('form-update-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'forms',
            ],
            [
                'id' => $formId,
                'objectType' => Form::class,
                'failMessage' => ['formId' => [__('tollerus::error.invalid_form')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'transliterated' => ['type' => 'string', 'column' => 'transliterated'],
            'phonemic'       => ['type' => 'string', 'column' => 'phonemic'],
            'irregular'      => ['type' => 'boolean', 'column' => 'irregular'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('form-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $formModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'string':
            default:
                $formModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $formModel->save();
            $this->dispatch('text-save-success', id: $domId);
            $this->refreshForm();
        } catch (\Throwable $e) {
            $this->dispatch('form-update-failure');
            throw $e;
        }
    }
    public function deleteForm(string $formId): void
    {
        Form::findOrFail((int)$formId)->delete();
        $this->refreshForm();
    }
    public function addFormValue(string $lexemeId, string $formId, string $valueId): void
    {
        // Find models
        $formModel = $this->findInCache('form-value-add-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'forms',
            ],
            [
                'id' => $formId,
                'objectType' => Form::class,
                'failMessage' => ['formId' => [__('tollerus::error.invalid_form')]],
            ],
        ]);
        $valueModel = FeatureValue::find($valueId);
        if (!($valueModel instanceof FeatureValue)) {
            $this->dispatch('form-value-add-failure');
            return;
        }
        // Create pivot row
        try {
            (new FormFeatureValue([
                'form_id' => $formModel->id,
                'feature_id' => $valueModel->feature_id,
                'value_id' => $valueModel->id,
            ]))->save();
        } catch (\Throwable $e) {
            $this->dispatch('form-value-add-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function removeFormValue(string $formId, string $valueId): void
    {
        FormFeatureValue::where('form_id', (int)$formId)
            ->where('value_id', (int)$valueId)
            ->firstOrFail()
            ->delete();
        $this->refreshForm();
    }
    public function updateNativeSpelling(string $lexemeId, string $formId, string $neographyId, string $spelling, ?string $domId = ''): void
    {
        // Find models
        $formModel = $this->findInCache('nativespelling-update-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'forms',
            ],
            [
                'id' => $formId,
                'objectType' => Form::class,
                'failMessage' => ['formId' => [__('tollerus::error.invalid_form')]],
            ],
        ]);
        $neographyModel = $this->language->neographies->firstWhere('id', $neographyId);
        if (!($neographyModel instanceof Neography)) {
            $this->dispatch('nativespelling-update-failure');
            return;
        }
        /**
         * In this case, we're using a more implicit UI pattern for
         * creating/deleting models.
         *
         * Basically, the user is given a text field and if it's
         * empty that means no model. If it's filled, that means
         * there's a model.
         *
         * So create/delete actions are triggered merely by state
         * changes in the text field.
         */
        $nativeSpelling = $formModel->nativeSpellings->firstWhere('neography_id', $neographyId);
        if (!($nativeSpelling instanceof NativeSpelling)) {
            if (empty($spelling)) {
                /**
                 * No NativeSpelling model exists, and the user has set the
                 * field to empty. That's already correct, so we do nothing.
                 */
                $this->refreshForm();
                return;
            } else {
                /**
                 * The user has typed a spelling, but no model exists. So
                 * we create one.
                 */
                $nativeSpelling = $formModel->nativeSpellings()->create([
                    'neography_id' => $neographyModel->id,
                ]);
            }
        }
        if (empty($spelling)) {
            /**
             * A model exists, but the user set the field to empty.
             * So we delete it.
             */
            $nativeSpelling->delete();
            $this->refreshForm();
            return;
        }
        /**
         * If we get this far, it means a model exists now (either
         * it pre-existed, or we just created it) and we need to
         * update it with what the user typed.
         */
        $nativeSpelling->spelling = $spelling;
        $nativeSpelling->save();
        $this->refreshForm();
    }
    public function createSense(string $lexemeId): void
    {
        // Find model
        $lexemeModel = $this->findInCache('sense-add-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
            ],
        ]);
        // Create sense
        $nextNum = $lexemeModel->senses->max('num') + 1;
        $lexemeModel->senses()->create([
            'num' => $nextNum,
        ]);
        // Renumber starting from 1
        $this->renumberSenses($lexemeId);
        $this->refreshForm();
    }
    public function updateSense(string $lexemeId, string $senseId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find models
        $senseModel = $this->findInCache('sense-update-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'senses',
            ],
            [
                'id' => $senseId,
                'objectType' => Sense::class,
                'failMessage' => ['senseId' => [__('tollerus::error.invalid_sense')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'body' => ['type' => 'string', 'column' => 'body'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('sense-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'string':
            default:
                $senseModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $senseModel->save();
            $this->dispatch('sense-update-success', id: $domId);
            $this->refreshForm();
        } catch (\Throwable $e) {
            $this->dispatch('sense-update-failure');
            throw $e;
        }
    }
    public function deleteSense(string $senseId): void
    {
        $sense = Sense::findOrFail((int)$senseId);
        $lexeme = $sense->lexeme;
        $sense->delete();
        // Renumber starting from 1
        $this->renumberSenses($lexeme->id);
        $this->refreshForm();
    }
    public function swapSenses(string $lexemeId, string $senseId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($lexemeId, $senseId, $neighborId) {
                $lexemeModel = collect($this->lexemes)->firstWhere('id', $lexemeId);
                $senseModel    = $lexemeModel->senses->firstWhere('id', $senseId);
                $neighborModel = $lexemeModel->senses->firstWhere('id', $neighborId);
                $oldSenseNum    = (int) $this->infoForm['lexemes'][$lexemeId]['senses'][$senseId]['num'];
                $oldNeighborNum = (int) $this->infoForm['lexemes'][$lexemeId]['senses'][$neighborId]['num'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minNum = $lexemeModel->senses->min('num');
                $neighborModel->num = $minNum - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $senseModel->num = $oldNeighborNum;
                $senseModel->save();
                $neighborModel->num = $oldSenseNum;
                $neighborModel->save();
                // Renumber starting from 1
                $this->renumberSenses($lexemeId);
            });
        } catch (\Throwable $e) {
            $this->dispatch('sense-swap-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function createSubsense(string $lexemeId, string $senseId): void
    {
        // Find models
        $senseModel = $this->findInCache('subsense-add-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'senses',
            ],
            [
                'id' => $senseId,
                'objectType' => Sense::class,
                'failMessage' => ['senseId' => [__('tollerus::error.invalid_sense')]],
            ],
        ]);
        // Create subsense
        $nextNum = $senseModel->subsenses->max('num') + 1;
        $senseModel->subsenses()->create([
            'num' => $nextNum,
        ]);
        $this->refreshForm();
    }
    public function updateSubsense(string $lexemeId, string $senseId, string $subsenseId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find models
        $subsenseModel = $this->findInCache('subsense-update-failure', [
            [
                'id' => $lexemeId,
                'objectType' => Lexeme::class,
                'failMessage' => ['lexemeId' => [__('tollerus::error.invalid_lexeme')]],
                'relation' => 'senses',
            ],
            [
                'id' => $senseId,
                'objectType' => Sense::class,
                'failMessage' => ['senseId' => [__('tollerus::error.invalid_sense')]],
                'relation' => 'subsenses',
            ],
            [
                'id' => $subsenseId,
                'objectType' => Subsense::class,
                'failMessage' => ['senseId' => [__('tollerus::error.invalid_subsense')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'body' => ['type' => 'string', 'column' => 'body'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('subsense-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'string':
            default:
                $senseModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $senseModel->save();
            $this->dispatch('subsense-update-success', id: $domId);
            $this->refreshForm();
        } catch (\Throwable $e) {
            $this->dispatch('subsense-update-failure');
            throw $e;
        }
    }
    public function deleteSubsense(string $subsenseId): void
    {
        Subsense::findOrFail((int)$subsenseId)->delete();
        $this->refreshForm();
    }
    public function swapSubsenses(string $lexemeId, string $senseId, string $subsenseId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($lexemeId, $senseId, $subsenseId, $neighborId) {
                $senseModel = collect($this->lexemes)->firstWhere('id', $lexemeId)->senses->firstWhere('id', $senseId);
                $subsenseModel = $senseModel->subsenses->firstWhere('id', $subsenseId);
                $neighborModel = $senseModel->subsenses->firstWhere('id', $neighborId);
                $oldSubsenseNum = (int) $this->infoForm['lexemes'][$lexemeId]['senses'][$senseId]['subsenses'][$subsenseId]['num'];
                $oldNeighborNum = (int) $this->infoForm['lexemes'][$lexemeId]['senses'][$senseId]['subsenses'][$neighborId]['num'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minNum = $senseModel->subsenses->min('num');
                $neighborModel->num = $minNum - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $subsenseModel->num = $oldNeighborNum;
                $subsenseModel->save();
                $neighborModel->num = $oldSubsenseNum;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('sense-swap-failure');
            throw $e;
        }
        $this->refreshForm();
    }

    /**
     * Public utility functions
     */
    public function renumberSenses(string $lexemeId): void
    {
        // Find model
        $lexemeModel = collect($this->lexemes)->firstWhere('id', $lexemeId);
        // Initialize important values
        $senses = $lexemeModel->senses->sortBy('num');
        $safeZero = min(0, $senses->min('num'));
        $count = $senses->count();
        // Renumber models
        $connection = config('tollerus.connection', 'tollerus');
        DB::connection($connection)->transaction(function () use ($senses, $safeZero, $count) {
            // Move all models out of the way
            foreach ($senses as $i => $sense) {
                $sense->num = $safeZero - $count + $i;
                $sense->save();
            }
            // Move them into the proper places
            foreach ($senses->values() as $i => $sense) {
                $sense->num = $i+1;
                $sense->save();
            }
        });
    }
}
