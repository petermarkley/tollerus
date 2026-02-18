<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
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

class PublicWordLookup extends Component
{
    #[Locked] public Collection $languages;
    public ?string $id;
    public SearchType $type;
    public ?string $key;
    public ?string $frag;
    public array $results = [];

    /**
     * Livewire hooks
     */
    public function render(Request $req): View
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' My test page';
        }

        // Initialize pessimistically
        $entry                 = null;
        $language              = null;
        $primaryNeography      = null;
        $neographies           = null;
        $multipleNeographies   = false;
        $primaryForm           = null;
        $primaryNativeSpelling = null;
        $lexemes               = null;
        // Conditionally populate
        if ($this->id !== null) {
            $entry = GlobalId::resolveId($this->id);
            if (!($entry instanceof Entry)) {
                abort(404);
            }
            $entry->loadMissing([
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
            $language            = $entry->language;
            $primaryNeography    = $language->primaryNeography;
            $neographies         = $language->neographies->sortBy('machine_name')->filter(fn ($n) => $n->visible || $n->id == $primaryNeography->id);
            $multipleNeographies = $primaryNeography !== null && $neographies->where('id', '!=', $primaryNeography->id)->isNotEmpty();
            $primaryForm         = $entry->primaryForm;
            $lexemes             = $entry->lexemes->sortBy('position')
                ->map(function ($lexeme) use ($primaryNeography) {
                    $group = $lexeme->wordClass->group;
                    $tables = $group->inflectionTables
                        ->where('visible', true)
                        ->sortBy('position')
                        ->map(function ($table) use ($lexeme, $primaryNeography) {
                            $columns = $table->columns
                                ->where('visible', true)
                                ->sortBy('position')
                                ->map(function ($column) use ($lexeme, $primaryNeography) {
                                    $rows = $column->rows
                                        ->where('visible', true)
                                        ->sortBy('position')
                                        ->map(function ($row) use ($column, $lexeme, $primaryNeography) {
                                            $filters = $column->filterValues->concat($row->filterValues);
                                            $form = $lexeme->forms->filter(
                                                fn ($form) => $filters->reduce(
                                                    fn ($carry, $filter) => $carry && $form->inflectionValues->contains($filter),
                                                    true
                                                )
                                            )->first();
                                            if ($form !== null && $primaryNeography !== null) {
                                                $formNative = $form->nativeSpellings->firstWhere('neography_id', $primaryNeography->id);
                                            } else {
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
        }

        return view('tollerus::livewire.public-word-lookup', [
                'entry'                 => $entry,
                'language'              => $language,
                'primaryNeography'      => $primaryNeography,
                'neographies'           => $neographies,
                'multipleNeographies'   => $multipleNeographies,
                'primaryForm'           => $primaryForm,
                'primaryNativeSpelling' => $primaryNativeSpelling,
                'lexemes'               => $lexemes,
            ])->layout('tollerus::components.layouts.public')
            ->title($pageTitle);
    }
    public function mount(Request $req): void
    {
        $this->languages = Language::where('visible', true)->get();

        /**
         * We need to check if the user has specified a global ID,
         * and if so we need to validate & resolve it.
         *
         * The only canonical variants of this page we should
         * accept are entry IDs. Other IDs underneath that should
         * redirect to the entry ID with the appropriate document
         * fragment inside it.
         *
         * The only other thing we want to catch is a glyph ID,
         * which we should redirect to an appropriate language
         * detail page (again, with document fragment).
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
                    $this->redirect(route('tollerus.public.languages.show', ['language' => $language]) . '#'.$this->id);
                break;
                case GlobalIdKind::Entry:
                    // Nothing to do
                break;
                case GlobalIdKind::Lexeme:
                    /**
                     * For a lexeme, we need to redirect to the entry
                     */
                    $entry = $obj->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id]) . '#'.$this->id);
                break;
                case GlobalIdKind::Form:
                    /**
                     * For a form, we also need to redirect to the entry
                     */
                    $entry = $obj->lexeme->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id]) . '#'.$this->id);
                break;
            }
        }

        $this->type = SearchType::tryFrom($req->query('type')) ?? SearchType::Transliterated;
        $this->key = $req->query('key', null);
        $this->search();
    }

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
                })->select([
                    'forms.*',
                    'ns.spelling as native',
                    'ns.sort_key as sort_key',
                    'l.primary_neography as primary_neography_id',
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
            $this->results = $formsQuery->get()->all();
        } else {
            $this->results = [];
        }
    }
}
