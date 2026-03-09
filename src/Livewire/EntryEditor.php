<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Domain\Morphology\Services\AutoInflector;
use PeterMarkley\Tollerus\Domain\Neography\Services\NativeKeyboard;
use PeterMarkley\Tollerus\Domain\Neography\Services\PhonemicKeyboard;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\InflectionColumn;
use PeterMarkley\Tollerus\Models\InflectionRow;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Sense;
use PeterMarkley\Tollerus\Models\Subsense;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\FormFeatureValue;
use PeterMarkley\Tollerus\Support\Markup\BodyTextNormalizer;
use PeterMarkley\Tollerus\Support\Markup\BodyTextSanitizer;

class EntryEditor extends Component
{
    // Models
    #[Locked] public Language $language;
    #[Locked] public Entry $entry;
    #[Locked] public array $lexemes;
    // UI input layer
    public array $infoForm = [];
    // UI display properties
    #[Locked] public array $wordClassGroups = [];
    #[Locked] public array $nativeKeyboards = [];
    #[Locked] public array $ipaKeyboard = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        if ($this->entry->primaryForm === null || empty($this->entry->primaryForm->transliterated)) {
            $entryName = __('tollerus::ui.entry_nameless');
        } else {
            $entryName = $this->entry->primaryForm->transliterated;
        }
        $pageTitle = mb_ucfirst($entryName);
        $neographyId = $this->language->primaryNeography?->id;
        return view('tollerus::livewire.entry-editor', [
                'entryName' => $entryName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layouts.admin', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'entries',
                    ]), 'text' => $this->language->name],
                ],
                'isLivewirePage' => true,
            ])->title($pageTitle);
    }
    public function mount(Language $language, Entry $entry): void
    {
        $this->entry = $entry;
        $this->language = $language;
        /**
         * The virtual keyboards on this page are partly for
         * typing in the WYSIWYG, where there's no reason to
         * restrict the user to neographies for this language.
         * Hence we do `loadAll()` instead of `loadForLanguage()`.
         */
        $this->nativeKeyboards = app(NativeKeyboard::class)->loadAll();
        $this->ipaKeyboard = app(PhonemicKeyboard::class)->load();

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
        $this->language->loadMissing([
            'wordClassGroups.wordClasses',
            'wordClassGroups.primaryClass',
            'wordClassGroups.features.featureValues',
            'wordClassGroups.inflectionTables.columns.filterValues',
            'wordClassGroups.inflectionTables.columns.rows.filterValues',
        ]);
        $this->lexemes = $this->entry->lexemes->sortBy('position')->all();

        /**
         * Prepare context for auto-inflection
         * ===================================
         *
         * In some ways the Tollerus data schema is designed to be very loose
         * and flexible. A WordClassGroup can have any number of tables and
         * columns, with any number of rows, each having any number of filters
         * that might overlap or leave gaps. Conversely a lexeme can have any
         * number of word forms with any number of grammar features assigned.
         * The correspondence between these two structures is completely
         * implicit.
         *
         * This avoids enforcing too much about how a conlanger wants to use
         * the system. But the downside is that we have to do extra work right
         * here to define that data correspondence and surface it to the user.
         *
         * For each inflection row, we need a list of all the word forms it
         * will match when the filters are applied.
         *
         *    - Wherever it's NOT one-to-one, inform the user.
         *
         *    - Wherever it IS one-to-one, offer auto-inflection.
         */
        $lexemes = collect($this->lexemes);
        $inflectionMatchesPerGroup = $this->language->wordClassGroups->mapWithKeys(function ($group) use ($lexemes) {
            /**
             * Find which best lexeme belongs to this group
             */
            // First, exclude any lexemes for a different group
            $matchedLexemes = $lexemes->sortBy('position')
                ->filter(fn ($l) => $l->wordClass->group_id == $group->id);
            // Next, segment by whether a lexeme contains any forms
            $lexemesWithForms = $matchedLexemes
                ->filter(fn ($l) => $l->forms->isNotEmpty());
            $lexemesWithNoForms = $matchedLexemes
                ->filter(fn ($l) => $l->forms->isEmpty());
            // Prefer one with forms if available
            if ($lexemesWithForms->isNotEmpty()) {
                $lexeme = $lexemesWithForms->first();
            } else {
                $lexeme = $lexemesWithNoForms->first();
            }
            // Don't assume that we found any lexemes at all
            if ($lexeme) {
                /**
                 * We could use `flatMap->columns->flatMap->rows`, except that
                 * we need to know the column filters when working on each row.
                 */
                $rows = $group->inflectionTables->flatMap->columns->map(function ($column) use ($group, $lexeme) {
                    return $column->rows->map(function ($row) use ($group, $column, $lexeme) {
                        /**
                         * We are now inside an inflection row. We now need to
                         * apply the column and row filters to all the forms on
                         * the relevant lexeme (if present).
                         */
                        // Column vs. row filters are treated the same
                        $filters = $column->filterValues->concat($row->filterValues);
                        // Package up the per-row data object
                        return [
                            'rowId' => $row->id,
                            'rowLabel' => $column->label." \u{2192} ".$row->label,
                            'srcBase' => $row->src_base,
                            'matchingForms' => $lexeme?->forms->filter(
                                fn ($form) => $filters->reduce(
                                    fn ($carry, $filter) => $carry && $form->inflectionValues->contains($filter),
                                    true
                                )
                            )->values(), // Collection of forms
                        ];
                    });
                })->flatten(1)->keyBy('rowId'); // Collection of rows
            } else {
                $rows = null;
            }
            return [$group->id => [
                'lexemeId' => $lexeme?->id,
                'rows' => $rows,
            ]];
        }); // Collection of groups
        /**
         * $inflectionMatchesPerGroup should now look something like:
         *
         *   [
         *     (WordClassGroup->id) => [
         *       'lexemeId' => <int>,
         *       'rows' => [
         *         (InflectionRow->id) => [
         *           'rowId' => <int>,
         *           'rowLabel' => <string>,
         *           'srcBase' => <int>,
         *           'matchingForms' => [
         *             0 => <Form::class>,
         *             . . .
         *           ],
         *         ],
         *         . . .
         *       ],
         *     ],
         *     . . .
         *   ]
         *
         * For non-inflected word class groups, the 'rows' key will be an
         * empty collection. For inflected groups with no lexeme, both it and
         * 'lexemeId' will be null. In either of these cases, do not warn the
         * user because nothing is amiss.
         *
         * For inflected groups with a lexeme, 'matchingForms' may still be an
         * empty collection or greater than 1:
         *
         *    $row['matchingForms']->count() != 1
         *
         * This is when we warn the user.
         *
         * However, even if count(matchingForms) is always 1, we still want to
         * check for a word form matching to multiple inflection rows. It must
         * be 1-to-1 in both directions.
         */

        $language = $this->language;
        $this->infoForm = [
            'primaryForm' => $this->entry->primary_form,
            'etym' => app(BodyTextNormalizer::class)->normalizeInlineForWysiwyg($this->entry->etym ?? ''),
            'lexemes' => collect($this->lexemes)->mapWithKeys(function ($lexeme) use ($language, $neographies, $inflectionMatchesPerGroup) {
                // Collate some info about inflection matching at the lexeme level
                $inflectionMatches = $inflectionMatchesPerGroup->get($lexeme->wordClass->group_id);
                $wasMatched = $inflectionMatches['lexemeId'] == $lexeme->id;
                if ($wasMatched && $inflectionMatches['rows']->isNotEmpty()) {
                    // This decides whether we warn the user
                    $hasMissingForms = $inflectionMatches['rows']->reduce(
                        fn ($carry, $row) => ($carry || $row['matchingForms']->isEmpty()),
                        false
                    );
                    // This decides whether we offer auto-inflection here
                    $canAutoInflect = (
                        // No missing or competing forms
                        $inflectionMatches['rows']->reduce(
                            fn ($carry, $row) => ($carry && $row['matchingForms']->count()==1),
                            true
                        )
                        &&
                        // ... and no forms matching multiple rows
                        $lexeme->forms->reduce(
                            fn ($carry, $form) => (
                                $carry
                                &&
                                $inflectionMatches['rows']
                                    ->filter(fn ($r) => $r['matchingForms']->contains($form))
                                    ->count() == 1
                            ),
                            true
                        )
                    );
                    $inflectionEditUrl = route('tollerus.admin.languages.inflections.edit', [
                        'language' => $language,
                        'wordClassGroup' => $lexeme->wordClass->group_id,
                    ]);
                } else {
                    $hasMissingForms = false;
                    $canAutoInflect = false;
                    $inflectionEditUrl = null;
                }
                return [$lexeme->id => [
                    'globalId' => $lexeme->global_id,
                    'wordClassId' => $lexeme->wordClass->id,
                    'wordClassName' => $lexeme->wordClass->name,
                    'wordClassGroupId' => $lexeme->wordClass->group_id,
                    'position' => $lexeme->position,
                    'wasMatched' => $wasMatched,
                    'hasMissingForms' => $hasMissingForms,
                    'canAutoInflect' => $canAutoInflect,
                    'inflectionEditUrl' => $inflectionEditUrl,
                    'forms' => $lexeme->forms->mapWithKeys(function ($form) use ($neographies, $inflectionMatches, $wasMatched, $lexeme) {
                        // Collate some info about inflection matching at the form level
                        $matchingRows     = $inflectionMatches['rows']->filter(fn ($row) => $row['matchingForms']->contains($form));
                        $matchingRow      = $matchingRows->first();
                        $matchingRowId    = $matchingRow['rowId'] ?? null;
                        $matchingRowLabel = $matchingRow['rowLabel'] ?? null;
                        $matchingRowCount = $matchingRows->count();
                        $srcRow           = $matchingRow['srcBase'] ?? null;
                        $srcRowForms = $inflectionMatches['rows']->get($srcRow)['matchingForms'] ?? null;
                        $srcForm     = $srcRowForms?->first()?->id;
                        $matchingRowHasOthers = ($matchingRow ? ($matchingRow['matchingForms']->count() > 1) : false);
                        $canAutoInflect = (
                            // We can auto-inflect if ...
                            $wasMatched // The lexeme has any matching status to begin with, AND
                            &&
                            $matchingRowCount == 1 // This form exists in only one row's match results, AND
                            &&
                            $matchingRow['matchingForms']->count() == 1 // It's the only one in that row's match results, AND
                            &&
                            $srcForm !== null // We have a valid base form to inflect from
                        );
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
                            })->values()->toArray(),
                            'inflectionValues' => $form->inflectionValues->mapWithKeys(function ($value) {
                                return [$value->id => [
                                    'featureId'   => $value->feature->id,
                                    'featureName' => $value->feature->name,
                                    'valueId'     => $value->id,
                                    'valueName'   => $value->name,
                                ]];
                            })->toArray(),
                            'matchingRowId'    => $matchingRowId,
                            'matchingRowLabel' => $matchingRowLabel,
                            'matchingRowCount' => $matchingRowCount,
                            'matchingRowHasOthers' => $matchingRowHasOthers,
                            'srcRow' => $srcRow,
                            'srcForm' => $srcForm,
                            'canAutoInflect' => $canAutoInflect,
                        ]];
                    })->toArray(),
                    'senses' => $lexeme->senses->mapWithKeys(function ($sense) {
                        return [$sense->id => [
                            'num' => $sense->num,
                            'usage' => $sense->usage,
                            'body' => app(BodyTextNormalizer::class)->normalizeInlineForWysiwyg($sense->body),
                            'subsenses' => $sense->subsenses->mapWithKeys(function ($subsense) {
                                return [$subsense->id => [
                                    'num' => $subsense->num,
                                    'usage' => $subsense->usage,
                                    'body' => app(BodyTextNormalizer::class)->normalizeInlineForWysiwyg($subsense->body),
                                ]];
                            })->toArray(),
                        ]];
                    })->toArray(),
                ]];
            })->toArray(),
        ];
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
                ])->values()->toArray(),
                'features' => $group->features->sortBy('name')->map(fn ($feature) => [
                    'id' => $feature->id,
                    'name' => $feature->name,
                    'values' => $feature->featureValues->sortBy('name')->map(fn ($value) => [
                        'id' => $value->id,
                        'name' => $value->name
                    ])->values()->toArray(),
                ])->values()->toArray(),
                'columns' => $group->inflectionTables
                    ->sortBy('position')
                    ->map(fn ($t) => $t->columns->sortBy('position'))
                    ->flatten(1)
                    ->map(fn ($column) => [
                        'id' => $column->id,
                        'label' => $column->label,
                        'rows' => $column->rows->sortBy('position')->map(fn ($row) => [
                            'id' => $row->id,
                            'label' => $row->label,
                        ])->values()->toArray(),
                    ])->values()->toArray(),
            ];
        })->values()->toArray();
    }
    public function infoSave(): void
    {
        try {
            $html = app(BodyTextSanitizer::class)->sanitize($this->infoForm['etym']);
            $htmlN = app(BodyTextNormalizer::class)->normalizeInlineForSave($html);
            $this->entry->etym = app(BodyTextNormalizer::class)->normalizeInlineForSave($html);
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
            'group.inflectionTables.columns.filterValues',
            'group.inflectionTables.columns.rows.filterValues',
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
             */
            if ($wordClassModel->group->inflectionTables->isNotEmpty()) {
                $this->createMissingFormsWorker($lexeme, $wordClassModel->group);
                $addedForm = true;
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
        app(GlobalIdGarbageCollector::class)->collect();
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
        $lexemeModel = Lexeme::find($lexemeId);
        if (!($lexemeModel instanceof Lexeme)) {
            $this->dispatch('form-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['lexemeId' => [__('tollerus::error.invalid_lexeme')]]);
        }
        // Create form
        $lexemeModel->forms()->create([
            'language_id' => $this->language->id,
        ]);
        $this->refreshForm();
    }
    public function updateForm(string $lexemeId, string $formId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $formModel = Form::find($formId);
        if (!($formModel instanceof Form)) {
            $this->dispatch('form-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['formId' => [__('tollerus::error.invalid_form')]]);
        }
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
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function addFormValue(string $lexemeId, string $formId, string $valueId): void
    {
        // Find models
        $formModel = Form::find($formId);
        if (!($formModel instanceof Form)) {
            $this->dispatch('form-value-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['formId' => [__('tollerus::error.invalid_form')]]);
        }
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
    public function matchFormToRow(string $lexemeId, string $formId, string $columnId, string $rowId): void
    {
        // Find models
        $lexemeModel = collect($this->lexemes)->firstWhere('id', $lexemeId);
        if (!($lexemeModel instanceof Lexeme)) {
            $this->dispatch('form-matchtorow-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['lexemeId' => [__('tollerus::error.invalid_lexeme')]]);
            return;
        }
        $formModel = $lexemeModel->forms->firstWhere('id', $formId);
        if (!($formModel instanceof Form)) {
            $this->dispatch('form-matchtorow-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['formId' => [__('tollerus::error.invalid_form')]]);
            return;
        }
        $wordClassGroup = $this->language->wordClassGroups->firstWhere('id', $lexemeModel->wordClass->group_id);
        $column = $wordClassGroup->inflectionTables->flatMap->columns->firstWhere('id', $columnId);
        if (!($column instanceof InflectionColumn)) {
            $this->dispatch('form-matchtorow-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['columnId' => [__('tollerus::error.invalid_inflection_column')]]);
            return;
        }
        $row = $column->rows->firstWhere('id', $rowId);
        if (!($row instanceof InflectionRow)) {
            $this->dispatch('form-matchtorow-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['rowId' => [__('tollerus::error.invalid_inflection_row')]]);
            return;
        }
        $column->loadMissing([
            'filterValues',
        ]);
        $row->loadMissing([
            'filterValues',
        ]);
        $formModel->loadMissing([
            'inflectionValues',
        ]);
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($formModel, $column, $row) {
                // Remove any existing inflection values
                foreach ($formModel->inflectionValues as $value) {
                    FormFeatureValue::where('form_id', (int)$formModel->id)
                        ->where('value_id', (int)$value->id)
                        ->firstOrFail()
                        ->delete();
                }
                // Add values from table and row filters
                $filters = $column->filterValues->concat($row->filterValues);
                foreach ($filters as $value) {
                    (new FormFeatureValue([
                        'form_id' => $formModel->id,
                        'feature_id' => $value->feature_id,
                        'value_id' => $value->id,
                    ]))->save();
                }
            });
        } catch (\Throwable $e) {
            $this->dispatch('form-matchtorow-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function updateNativeSpelling(string $lexemeId, string $formId, string $neographyId, string $spelling, ?string $domId = ''): void
    {
        // Find models
        $formModel = Form::find($formId);
        if (!($formModel instanceof Form)) {
            $this->dispatch('nativespelling-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['formId' => [__('tollerus::error.invalid_form')]]);
        }
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
        $lexemeModel = Lexeme::find($lexemeId);
        if (!($lexemeModel instanceof Lexeme)) {
            $this->dispatch('sense-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['lexemeId' => [__('tollerus::error.invalid_lexeme')]]);
        }
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
        $senseModel = Sense::find($senseId);
        if (!($senseModel instanceof Sense)) {
            $this->dispatch('sense-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['senseId' => [__('tollerus::error.invalid_sense')]]);
        }
        // $propName whitelist
        $allowedPropData = [
            'usage' => ['type' => 'string', 'column' => 'usage'],
            'body' => ['type' => 'html', 'column' => 'body'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('sense-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'html':
                $html = app(BodyTextSanitizer::class)->sanitize($propVal);
                $senseModel[$allowedPropData[$propName]['column']] = app(BodyTextNormalizer::class)->normalizeInlineForSave($html);
            break;
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
        $senseModel = Sense::find($senseId);
        if (!($senseModel instanceof Sense)) {
            $this->dispatch('subsense-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['senseId' => [__('tollerus::error.invalid_sense')]]);
        }
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
        $subsenseModel = Subsense::find($subsenseId);
        if (!($subsenseModel instanceof Subsense)) {
            $this->dispatch('subsense-update-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['senseId' => [__('tollerus::error.invalid_subsense')]]);
        }
        // $propName whitelist
        $allowedPropData = [
            'usage' => ['type' => 'string', 'column' => 'usage'],
            'body' => ['type' => 'html', 'column' => 'body'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('subsense-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'html':
                $html = app(BodyTextSanitizer::class)->sanitize($propVal);
                $subsenseModel[$allowedPropData[$propName]['column']] = app(BodyTextNormalizer::class)->normalizeInlineForSave($html);
            break;
            case 'string':
            default:
                $subsenseModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $subsenseModel->save();
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
    public function createMissingForms(string $lexemeId): void
    {
        // Find model
        $lexemeModel = Lexeme::find($lexemeId);
        if (!($lexemeModel instanceof Lexeme)) {
            $this->dispatch('form-addmissing-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['lexemeId' => [__('tollerus::error.invalid_lexeme')]]);
        }
        $this->createMissingFormsWorker($lexemeModel, $lexemeModel->wordClass->group);
        $this->refreshForm();
    }
    public function autoInflect(string $lexemeId, string $formId, string $rowId, string $baseStr, string $type, ?string $neographyId = null, ?string $domId = ''): void
    {
        if (mb_strlen($baseStr) == 0) {
            return;
        }
        /**
         * Find models
         */
        // Form
        $formModel = Form::find($formId);
        if (!($formModel instanceof Form)) {
            $this->dispatch('form-autoinflect-failure', id: $domId);
            throw \Illuminate\Validation\ValidationException::withMessages(['formId' => [__('tollerus::error.invalid_form')]]);
        }
        // Inflection row (directly)
        $row = InflectionRow::find($rowId);
        if (!($row instanceof InflectionRow)) {
            $this->dispatch('form-autoinflect-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['rowId' => [__('tollerus::error.invalid_inflection_row')]]);
            return;
        }
        // Backed enum instance
        try {
            $patternType = MorphRulePatternType::from($type);
        } catch (Throwable $e) {
            $this->dispatch('form-autoinflect-failure');
            return;
        }
        /**
         * Perform auto-inflection
         */
        $result = new AutoInflector(
            row: $row,
            base: $baseStr,
            type: $patternType,
            neographyId: $neographyId,
        )->inflect();
        switch ($patternType) {
            case MorphRulePatternType::Transliterated:
                $formModel->transliterated = $result;
            break;
            case MorphRulePatternType::Phonemic:
                $formModel->phonemic = $result;
            break;
            case MorphRulePatternType::Native:
                $this->updateNativeSpelling($lexemeId, $formId, $neographyId, $result, $domId);
            break;
        }
        // Save to database
        try {
            $formModel->save();
            $this->dispatch('form-autoinflect-success', id: $domId);
        } catch (\Throwable $e) {
            $this->dispatch('form-autoinflect-failure');
            throw $e;
        }
        $this->refreshForm();
    }

    /**
     * Internal utility functions
     */
    private function createMissingFormsWorker(Lexeme $lexeme, WordClassGroup $group): void
    {
        /**
         * For lexemes in inflected word class groups, the system expects
         * exactly one word form to exist for each inflection row, with
         * grammar features that match it one-to-one.
         *
         * To help the user achieve this state, we're going to add any word
         * forms that are missing.
         */
        // Check for bad input
        if ($lexeme->wordClass->group_id != $group->id) {
            return;
        }
        // Eager-load relations
        $lexeme->loadMissing([
            'forms.inflectionValues'
        ]);
        $group->loadMissing([
            'inflectionTables.columns.filterValues',
            'inflectionTables.columns.rows.filterValues',
        ]);
        // Check for non-inflected word class group
        if (!$group->features()->exists() || $group->inflectionTables->isEmpty()) {
            return;
        }
        // Okay, let's do it
        foreach ($group->inflectionTables->sortBy('position') as $table) {
            foreach ($table->columns->sortBy('position') as $column) {
                foreach ($column->rows->sortBy('position') as $row) {

                    // Establish filter list
                    $filters = $column->filterValues->concat($row->filterValues);

                    // Check if any forms already match
                    $matchingForms = $lexeme->forms->filter(
                        fn ($form) => $filters->reduce(
                            fn ($carry, $filter) => $carry && $form->inflectionValues->contains($filter),
                            true
                        )
                    );
                    if ($matchingForms->isNotEmpty()) {
                        // If one or more matching form exists, then skip this row
                        continue;
                    }

                    // Create the form
                    $form = $lexeme->forms()->create([
                        'language_id' => $this->language->id,
                    ]);

                    // Maybe set as primary
                    if ($row->src_base === null && $this->entry->primary_form === null) {
                        $this->entry->primary_form = $form->id;
                        $this->entry->save();
                    }

                    // Add filter values
                    foreach ($filters as $value) {
                        (new FormFeatureValue([
                            'form_id' => $form->id,
                            'feature_id' => $value->feature_id,
                            'value_id' => $value->id,
                        ]))->save();
                    }

                }
            }
        }
    }
}
