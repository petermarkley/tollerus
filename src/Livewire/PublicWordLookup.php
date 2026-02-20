<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Enums\SearchType;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Neography;

class PublicWordLookup extends Component
{
    // Models
    #[Locked] public Collection $languages;
    #[Locked] public Collection $neographies;
    // Page parameters
    #[Url(history: false)] public ?string $id = null;
    #[Url(history: true)] public SearchType $type = SearchType::Transliterated;
    #[Url(history: true)] public ?string $key = null;
    #[Url(as: 'hl', history: true)] public ?string $highlight = null;
    // Internal state
    public ?Entry $entry = null;
    public ?GlobalIdKind $highlightKind = null;
    public array $results = [];
    // Cache of entry display info
    private ?string $displayedId = null;
    private array $display = [];

    /**
     * Livewire hooks
     */
    public function render(Request $req): View
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' My test page';
        }

        // Conditionally populate
        if ($this->id !== $this->displayedId) {
            $this->displayEntry();
        }

        return view('tollerus::livewire.public-word-lookup', $this->display)
            ->layout('tollerus::components.layouts.public')
            ->title($pageTitle);
    }
    public function mount(Request $req): void
    {
        // Initialize models
        $this->languages = Language::where('visible', true)
            ->orderBy('machine_name')
            ->get();
        $this->neographies = Neography::all()
            ->filter(fn ($n) => $n->visible || $n->languagesWherePrimary()->exists());

        /**
         * We need to check if the user has specified a global ID,
         * and if so we need to validate & resolve it.
         */
        $this->id = $req->query('id', null);
        if ($this->id !== null) {
            // Look up global ID and its associated model
            $globalId = GlobalId::fromStr($this->id);
            if (!($globalId instanceof GlobalId)) {
                abort(404);
            }
            $obj = $globalId->resolve();
            // What kind of model is this?
            switch ($globalId->kind) {
                case GlobalIdKind::Glyph:
                    /**
                     * This is a glyph inside a neography. Let's try to
                     * redirect to an appropriate language page.
                     */
                    $neography = $obj->neography;
                    $language = $neography->languagesWherePrimary->firstWhere('visible', true) ?? $neography->languages->firstWhere('visible', true);
                    if (!($language instanceof Language)) {
                        abort(404);
                    }
                    $this->redirect(route('tollerus.public.languages.show', ['language' => $language, 'hl' => $this->id]));
                break;
                case GlobalIdKind::Entry:
                    $this->entry = $obj;
                    $this->highlight = $obj->primaryForm?->global_id;
                    $this->highlightKind = GlobalIdKind::Entry;
                break;
                case GlobalIdKind::Lexeme:
                    /**
                     * For a lexeme, we need to redirect to the entry
                     */
                    $entry = $obj->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id, 'hl' => $this->id]));
                break;
                case GlobalIdKind::Form:
                    /**
                     * For a form, we also need to redirect to the entry
                     */
                    $entry = $obj->lexeme->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id, 'hl' => $this->id]));
                break;
            }
        }

        // Initialize page state
        $this->type = SearchType::tryFrom($req->query('type')) ?? SearchType::Transliterated;
        $this->key = $req->query('key', null);
        $this->search();
    }

    /**
     * Page interactions
     */
    public function search(): void
    {
        $rawConnection = DB::connection(config('tollerus.connection'));
        $prefix = $rawConnection->getTablePrefix();
        if ($this->key !== null && strlen($this->key) > 0) {
            $formsQuery = Form::query()
                ->join('languages as l', 'l.id', '=', 'forms.language_id')
                ->leftJoin('native_spellings as ns', function ($join) {
                    $join->on('ns.form_id', '=', 'forms.id')
                        ->on('ns.neography_id', '=', 'l.primary_neography');
                })->leftJoin('neographies as pn', function ($join) {
                    $join->on('pn.id', '=', 'l.primary_neography');
                })
                ->select([
                    'forms.*',
                    'ns.spelling as native',
                    'ns.sort_key as sort_key',
                    'l.primary_neography as primary_neography_id',
                    'l.machine_name as languageMachineName',
                    'pn.machine_name as primaryNeographyMachineName',
                ]);
            switch ($this->type) {
                case SearchType::Transliterated:
                    $formsQuery
                        ->selectRaw("
                            CASE
                            WHEN {$prefix}forms.transliterated = ? THEN 0
                            WHEN {$prefix}forms.transliterated LIKE ? THEN 1
                            WHEN {$prefix}forms.transliterated LIKE ? THEN 2
                            ELSE 3
                            END AS relevance
                        ", [$this->key, $this->key.'%', '%'.$this->key.'%'])
                        ->orderBy('relevance')
                        ->orderByRaw("CHAR_LENGTH({$prefix}forms.transliterated) ASC")
                        ->orderBy('forms.transliterated');
                    $formsQuery->where('forms.transliterated', 'like', '%'.$this->key.'%');
                break;
                case SearchType::Native:
                    $formsQuery
                        ->selectRaw("
                            CASE
                            WHEN {$prefix}ns.spelling = ? THEN 0
                            WHEN {$prefix}ns.spelling LIKE ? THEN 1
                            WHEN {$prefix}ns.spelling LIKE ? THEN 2
                            ELSE 3
                            END AS relevance
                        ", [$this->key, $this->key.'%', '%'.$this->key.'%'])
                        ->orderBy('relevance')
                        ->orderByRaw("CHAR_LENGTH({$prefix}ns.spelling) ASC")
                        ->orderBy('ns.spelling');
                    $formsQuery->where('ns.spelling', 'like', '%'.$this->key.'%');
                break;
                case SearchType::Definition:
                    //
                break;
            }
            $results = $formsQuery->get();
            $results->load('lexeme.entry');
            $resultsFinal = $results->map(function ($result) {
                $entry = $result->lexeme->entry;
                $result['entryGlobalId'] = $entry->global_id;
                $result['entryPrimaryFormId'] = $entry->primary_form;
                $result['isPrimary'] = $result['entryPrimaryFormId'] === $result['id'];
                return $result;
            })->toArray();
            $this->results = $resultsFinal;
        } else {
            $this->results = [];
        }
    }
    public function selectResult(?string $globalIdStr, bool $updateParams = true): void
    {
        if ($globalIdStr === null) {
            return;
        }
        // Look up global ID and its associated model
        $globalId = GlobalId::fromStr($globalIdStr);
        if (!($globalId instanceof GlobalId)) {
            return;
        }
        $obj = $globalId->resolve();
        // What kind of model is this?
        switch ($globalId->kind) {
            case GlobalIdKind::Glyph:
                /**
                 * This is a glyph inside a neography. Let's try to
                 * redirect to an appropriate language page.
                 */
                $neography = $obj->neography;
                $language = $neography->languagesWherePrimary->firstWhere('visible', true) ?? $neography->languages->firstWhere('visible', true);
                if (!($language instanceof Language)) {
                    return;
                }
                $this->redirect(route('tollerus.public.languages.show', ['language' => $language]) . '#'.$this->id);
            break;
            case GlobalIdKind::Entry:
                $this->entry = $obj;
                if ($updateParams) {
                    $this->id = $globalIdStr;
                    $this->highlight = $obj->primaryForm?->global_id;
                    $this->highlightKind = GlobalIdKind::Entry;
                }
            break;
            case GlobalIdKind::Lexeme:
                $this->entry = $obj->entry;
                if ($updateParams) {
                    $this->id = $this->entry->global_id;
                    $this->highlight = $obj->global_id;
                    $this->highlightKind = GlobalIdKind::Lexeme;
                }
            break;
            case GlobalIdKind::Form:
                $this->entry = $obj->lexeme->entry;
                if ($updateParams) {
                    $this->id = $this->entry->global_id;
                    $this->highlight = $obj->global_id;
                    $this->highlightKind = GlobalIdKind::Form;
                }
            break;
        }
    }
    private function displayEntry(): void
    {
        // Initialize pessimistically
        $language              = null;
        $primaryNeography      = null;
        $languageNeographies   = null;
        $multipleNeographies   = false;
        $primaryForm           = null;
        $primaryNativeSpelling = null;
        $lexemes               = null;
        $selectedResult        = null;
        // Conditionally populate
        if ($this->id !== null) {
            if (!($this->entry instanceof Entry)) {
                abort(404);
            }
            $this->entry->loadMissing([
                'language.neographies',
                'language.primaryNeography',
                'primaryForm.nativeSpellings',
                'lexemes.forms.nativeSpellings',
                'lexemes.forms.inflectionValues',
                'lexemes.wordClass.group.features.featureValues',
                'lexemes.wordClass.group.inflectionTables.columns.filterValues',
                'lexemes.wordClass.group.inflectionTables.columns.rows.filterValues',
                'lexemes.senses.subsenses',
            ]);
            $language            = $this->entry->language;
            $primaryNeography    = $language->primaryNeography;
            $languageNeographies = $language->neographies->sortBy('machine_name')->filter(fn ($n) => $n->visible || $n->id == $primaryNeography->id);
            $multipleNeographies = $primaryNeography !== null && $languageNeographies->count() > 1;
            $primaryForm         = $this->entry->primaryForm;
            $lexemes             = $this->entry->lexemes->sortBy('position')
                ->map(function ($lexeme) use ($primaryNeography) {
                    $group = $lexeme->wordClass->group;

                    /**
                     * These data lookups facilitate a higher-performance
                     * matching algorithm once we start processing each
                     * inflection row. The difference is very noticeable
                     * in the UI for large inflection tables (50+ forms).
                     *
                     * Eloquent model lookup
                     * [
                     *   <formId> => <Form::class>,
                     *   <formId> => <Form::class>,
                     *   ...
                     * ]
                     */
                    $formsById = $lexeme->forms->keyBy('id')->all();
                    /**
                     * Cross-lookup from filter values to forms
                     * [
                     *   <featureValueId> => [
                     *     <formId>,
                     *     <formId>,
                     *     ...
                     *   ]
                     *   ...
                     * ]
                     */
                    $formIdsByValueId = $lexeme->forms
                        ->flatMap(
                            fn ($f) => $f->inflectionValues
                                ->pluck('id')
                                ->map(fn ($vid) => [$vid => $f->id])
                        )->mapToGroups(fn ($pair) => $pair)
                        ->toArray();

                    $tables = $group->inflectionTables
                        ->where('visible', true)
                        ->sortBy('position')
                        ->map(function ($table) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                            $columns = $table->columns
                                ->where('visible', true)
                                ->sortBy('position')
                                ->map(function ($column) use ($lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                    $rows = $column->rows
                                        ->where('visible', true)
                                        ->sortBy('position')
                                        ->map(function ($row) use ($column, $lexeme, $formsById, $formIdsByValueId, $primaryNeography) {
                                            $filters = $column->filterValues->concat($row->filterValues);
                                            /**
                                             * Conceptually we are asking "which forms on this
                                             * lexeme have [such and such] inflection value?"
                                             *
                                             * For example if we're rendering English verbs and this
                                             * is the row for "3rd pers. sing.", the list of
                                             * column + row filter values is:
                                             *
                                             * [finite, present, simple, 3rd person, singular]
                                             *
                                             * So we ask:
                                             *  1. Which verb forms are finite?
                                             *  2. Which _of those_ are present tense?
                                             *  3. Which _of those_ are simple aspect?
                                             * ... and so on.
                                             *
                                             * Thus we whittle the list of forms down as we go. We
                                             * then render the first form that still remains at the
                                             * end (if there is one).
                                             *
                                             * However we will create an optimized filter list for
                                             * faster comparison:
                                             *  - It uses our precomputed cross-lookup
                                             *  - Allows us to rule out non matches earlier
                                             */
                                            $filterIds = $filters->pluck('id')
                                                ->sortBy(fn ($filterId) => count($formIdsByValueId[$filterId] ?? []))
                                                ->values();

                                            /**
                                             * For benchmark notes on alternative implementations:
                                             * `docs/performance/inflection-matching.md`
                                             */
                                            $formId = $filterIds->reduce(
                                                function ($candidates, $filterId) use ($formIdsByValueId) {
                                                    if ($candidates->isEmpty()) {
                                                        // Set is empty, so don't call `->intersect()` anymore
                                                        return $candidates;
                                                    }
                                                    // Compare, and whittle down the list of forms ...
                                                    return $candidates->intersect($formIdsByValueId[$filterId] ?? []);
                                                },
                                                // Start with all forms on the lexeme
                                                collect(array_keys($formsById))
                                            )->first(); // Done, now pick the first remaining form

                                            if ($formId !== null && $primaryNeography !== null) {
                                                $form = $formsById[$formId];
                                                $formNative = $form->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
                                            } else {
                                                $form = null;
                                                $formNative = null;
                                            }
                                            return [
                                                'model' => $row,
                                                'form' => $form,
                                                'formNative' => $formNative,
                                            ];
                                        })->values();
                                    return [
                                        'model' => $column,
                                        'rows' => $rows,
                                    ];
                                })->values();
                            return [
                                'model' => $table,
                                'columns' => $columns,
                            ];
                        })->values();
                    return [
                        'model' => $lexeme,
                        'class' => $lexeme->wordClass,
                        'group' => $group,
                        'tables' => $tables,
                    ];
                })->values();
            if ($primaryNeography !== null && $primaryForm !== null) {
                $primaryNativeSpelling = $primaryForm->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
            }
            $selectedResultObj = collect($this->results)->first(
                fn ($result) =>
                    ($this->id === $result['entryGlobalId'] && $this->highlightKind != GlobalIdKind::Form && $result['isPrimary']) ||
                    $this->highlight === $result['global_id']
            );
            if ($selectedResultObj) {
                $selectedResult = $selectedResultObj['global_id'];
            }
        }
        // Store the final values
        $this->display['language']              = $language;
        $this->display['primaryNeography']      = $primaryNeography;
        $this->display['languageNeographies']   = $languageNeographies;
        $this->display['multipleNeographies']   = $multipleNeographies;
        $this->display['primaryForm']           = $primaryForm;
        $this->display['primaryNativeSpelling'] = $primaryNativeSpelling;
        $this->display['lexemes']               = $lexemes;
        $this->display['selectedResult']        = $selectedResult;
    }

    /**
     * Livewire `#[Url]` hooks
     */
    public function updatedId($value): void
    {
        $this->selectResult($value, false);
    }
    public function updatedHighlight($value): void
    {
        $this->selectResult($value ?? $this->id, false);
    }
    public function updatedType(): void
    {
        $this->search();
    }
    public function updatedKey(): void
    {
        $this->search();
    }
}
