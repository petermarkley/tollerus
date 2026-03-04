<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Models\Entry;
use PeterMarkley\Tollerus\Models\Form;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\NativeSpelling;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographyGlyph;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\WordClass;

class WordPicker extends Component
{
    public bool $softLimitToParticles = false;
    #[Locked] public bool $showParticleToggle = false;
    #[Locked] public bool $requireForm = false;
    #[Locked] public ?Language $language = null;
    #[Locked] public array $particleClasses = [];
    #[Locked] public array $particleClassIds = [];
    public ?string $selectedWordId = null;
    #[Locked] public ?GlobalIdKind $selectedWordKind = null;
    #[Locked] public NeographyGlyph|Entry|Form|null $selectedWord = null;
    #[Locked] public string $selectedWordTransliterated = '';
    #[Locked] public string $selectedWordNative = '';
    #[Locked] public ?Neography $selectedWordNativeNeography = null;
    #[Locked] public ?string $selectedWordEditUrl = null;
    public string $searchKey = '';
    #[Locked] public array $results = [];

    /**
     * Internal search query params
     */
    private const int MAX_ENTRIES = 20; // Number of entry groups to display
    private const int MAX_FORMS_PER_ENTRY = 6; // Number of forms shown under each entry
    private const int MAX_ROWS_SCAN = 250; // How many matched forms we scan before grouping

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        //
        return view('tollerus::livewire.word-picker');
    }
    public function mount(
        bool $softLimitToParticles = false,
        bool $requireForm = false,
        ?Language $language = null,
        ?string $selectedWordId = null,
    ): void
    {
        $this->softLimitToParticles = $softLimitToParticles;
        $this->showParticleToggle = $softLimitToParticles;
        if ($language?->exists) {
            /**
             * Apparently when we type-hint a `mount()` param to a model,
             * if the parent view provides none then we get a hollow
             * model instance instead of `null`.
             */
            $this->language = $language;
            $this->particleClasses = $language->wordClasses()->whereIn('name', config('tollerus.particle_word_classes'))->get()->all();
        } else {
            $this->particleClasses = WordClass::whereIn('name', config('tollerus.particle_word_classes'))->get()->all();
        }
        $this->particleClassIds = collect($this->particleClasses)->pluck('id')->toArray();
        if ($selectedWordId === null) {
            $this->deselectWord();
        } else {
            $this->selectWord($selectedWordId);
        }
        $this->refreshForm();
    }

    /**
     * Internal logic
     */
    private function buildWord(string $globalId, GlobalIdKind $kind, NeographyGlyph|Entry|Form $obj): array
    {
        $word = [
            'globalId' => $globalId,
            'kind' => $kind,
        ];
        /**
         * We need make a best attempt at populating
         * the transliterated and native spellings.
         */
        switch ($kind) {
            case GlobalIdKind::Glyph:
                $word['neography'] = $obj->neography;
                $word['neographyMachineName'] = $word['neography']?->machine_name;
                if (!empty($obj->transliterated)) {
                    $word['transliterated'] = $obj->transliterated;
                    $word['native'] = $obj->glyph;
                } else if (!empty($obj->pronunciation_transliterated)) {
                    $word['transliterated'] = $obj->pronunciation_transliterated;
                    $word['native'] = $obj->pronunciation_native;
                } else {
                    $word['transliterated'] = '';
                    $word['native'] = '';
                }
                $word['editUrl'] = route('tollerus.admin.neographies.glyphs.edit', [
                    'neography' => $word['neography'],
                    'section' => $obj->group->section_id,
                ]);
            break;
            case GlobalIdKind::Entry:
                $word['neography'] = $obj->language->primaryNeography;
                $word['neographyMachineName'] = $word['neography']?->machine_name;
                // First-class scenario: entry's primary form
                $form = $obj->primaryForm;
                // Fallback: first form that we find
                if (!($form instanceof Form)) {
                    $form = Form::query()
                        ->whereHas('lexeme', fn ($q) => $q->where('entry_id', $obj->id))
                        ->orderBy('id')
                        ->first();
                }
                if ($form instanceof Form) {
                    // Success! We found a valid form for the entry
                    $word['transliterated'] = $form->transliterated;
                    $nativeSpelling = $form->nativeSpellings()
                        ->where('neography_id', $word['neography']->id)
                        ->first();
                    if ($nativeSpelling instanceof NativeSpelling) {
                        $word['native'] = $nativeSpelling->spelling;
                    } else {
                        $word['native'] = '';
                    }
                } else {
                    // No form, we have to give up
                    $word['transliterated'] = '';
                    $word['native'] = '';
                }
                $word['editUrl'] = route('tollerus.admin.languages.entries.edit', [
                    'language' => $obj->language,
                    'entry' => $obj,
                ]);
            break;
            case GlobalIdKind::Lexeme:
                $entry = $obj->entry;
                $word['neography'] = $entry->language->primaryNeography;
                $word['neographyMachineName'] = $word['neography']?->machine_name;
                // First-class scenario: entry's primary form
                $form = $entry->primaryForm;
                // Fallback: first form that we find
                if (!($form instanceof Form)) {
                    $form = $obj->forms()->first();
                }
                if ($form instanceof Form) {
                    // Success! We found a valid form for the entry
                    $word['transliterated'] = $form->transliterated;
                    $nativeSpelling = $form->nativeSpellings()
                        ->where('neography_id', $word['neography']->id)
                        ->first();
                    if ($nativeSpelling instanceof NativeSpelling) {
                        $word['native'] = $nativeSpelling->spelling;
                    } else {
                        $word['native'] = '';
                    }
                } else {
                    // No form, we have to give up
                    $word['transliterated'] = '';
                    $word['native'] = '';
                }
                $word['editUrl'] = route('tollerus.admin.languages.entries.edit', [
                    'language' => $entry->language,
                    'entry' => $entry,
                ]);
            break;
            case GlobalIdKind::Form:
                $word['neography'] = $obj->language->primaryNeography;
                $word['neographyMachineName'] = $word['neography']?->machine_name;
                $word['transliterated'] = $obj->transliterated;
                $nativeSpelling = $obj->nativeSpellings()
                    ->where('neography_id', $word['neography']->id)
                    ->first();
                if ($nativeSpelling instanceof NativeSpelling) {
                    $word['native'] = $nativeSpelling->spelling;
                } else {
                    $word['native'] = '';
                }
                $word['editUrl'] = route('tollerus.admin.languages.entries.edit', [
                    'language' => $obj->language,
                    'entry' => $obj->lexeme->entry_id,
                ]);
            break;
        }
        return $word;
    }

    /**
     * Page interactions
     */
    public function deselectWord()
    {
        $this->selectedWordId = '';
        $this->selectedWordKind = null;
        $this->selectedWord = null;
        $this->selectedWordTransliterated = '';
        $this->selectedWordNative = '';
        $this->selectedWordNativeNeography = null;
        $this->selectedWordEditUrl = null;
    }
    public function selectWord(string $selectedWordId)
    {
        /**
         * Validate global ID
         */
        $globalId = GlobalId::fromStr($selectedWordId);
        if (!($globalId instanceof GlobalId) || !(
            $globalId->kind == GlobalIdKind::Glyph ||
            $globalId->kind == GlobalIdKind::Entry ||
            $globalId->kind == GlobalIdKind::Form
        )) {
            $this->deselectWord();
            return;
        }
        $this->selectedWordId = $selectedWordId;
        $this->selectedWordKind = $globalId->kind;
        $obj = $globalId->resolve();
        $this->selectedWord = $obj;
        $word = $this->buildWord($selectedWordId, $globalId->kind, $obj);
        $this->selectedWordTransliterated = $word['transliterated'];
        $this->selectedWordNative = $word['native'];
        $this->selectedWordNativeNeography = $word['neography'];
        $this->selectedWordEditUrl = $word['editUrl'];
    }
    public function refreshForm()
    {
        $this->searchKey = '';
        $this->results = [];
    }
    public function search()
    {
        $this->results = [];
        /**
         * First we check if the user pasted a global ID
         */
        $globalId = GlobalId::fromStr($this->searchKey);
        if ($globalId instanceof GlobalId) {
            $obj = $globalId->resolve();
            // Check if we're out of bounds ...
            if ($this->language !== null) {
                switch ($globalId->kind) {
                    case GlobalIdKind::Glyph:
                        if (!$obj->neography->languages->contains($this->language)) {
                            return;
                        }
                    break;
                    default:
                        if ($obj->language != $this->language) {
                            return;
                        }
                    break;
                }
            }
            // We're okay, proceed with populating result ...
            if ($globalId->kind == GlobalIdKind::Lexeme) {
                /**
                 * Lexemes get special treatment. We will recognize them
                 * but offer only related IDs to actually select.
                 */
                $entry = $obj->entry;
                $this->results[] = $this->buildWord($entry->global_id, GlobalIdKind::Entry, $entry);
                $forms = $obj->forms;
                foreach ($forms as $form) {
                    $this->results[] = $this->buildWord($form->global_id, GlobalIdKind::Form, $form);
                }
            } else {
                /**
                 * Glyphs, Entries, and Forms are all selectable directly.
                 */
                $this->results[] = $this->buildWord($this->searchKey, $globalId->kind, $obj);
            }
        } else {
            /**
             * Alright, we need to build a couple of monster queries.
             * Let's think through what we're doing.
             *
             * Fields to match against the search key:
             * - neography_glyphs.transliterated
             * - neography_glyphs.pronunciation_transliterated
             * - forms.transliterated
             *
             * We need each glyph to become:
             * [
             *   'globalId' => ... ,
             *   'kind' => GlobalIdKind::Glyph,
             *   'neographyMachineName' => ... , // accessed through neography_id
             *   'transliterated' => ... ,  // (empty(transliterated) ? pronunciation_transliterated : transliterated)
             *   'native' => ... , // (empty(transliterated) ? pronunciation_native : glyph)
             * ]
             *
             * We need matching forms to be grouped by their `lexeme_id`'s `entry_id`.
             *
             * Each group needs to begin with an extra row for the entry itself.
             *
             * The entry row needs to become:
             * [
             *   'globalId' => ... ,
             *   'kind' => GlobalIdKind::Entry,
             *   'neographyMachineName' => ... , // the `language_id`'s `primary_neography` (or empty/null)
             *   'transliterated' => ... , // the `primary_form`'s `transliterated` (or empty/null)
             *   'native' => ... , // the `primary_form`'s `native_spellings` row whose `neography_id` matches the form's `language_id`'s `primary_neography` (or empty/null)
             * ]
             *
             * Then each form underneath the entry becomes:
             * [
             *   'globalId' => ... ,
             *   'kind' => GlobalIdKind::Form,
             *   'neographyMachineName' => ... , // the `language_id`'s `primary_neography` (or empty/null)
             *   'transliterated' => ... , // just the `transliterated` column on the form
             *   'native' => ... , // the form's `native_spellings` row whose `neography_id` matches the form's `language_id`'s `primary_neography` (or empty/null)
             * ]
             *
             * Groups must remain together through any sorting.
             */
            $rawConnection = DB::connection(config('tollerus.connection'));
            $prefix = $rawConnection->getTablePrefix();
            $key = trim((string) $this->searchKey);
            if ($key === '') {
                $this->results = [];
                return;
            }

            /**
             * ===================================================
             *                    FORMS QUERY
             * ===================================================
             *
             * The `forms` table is our first match target, but we
             * need JOINs on:
             *
             *   - lexemes (for checking for particles),
             *
             *   - entries (for building entry headers),
             *
             *   - languages (for language lock, and checking
             *     primary neography),
             *
             *   - neographies (for selecting native spellings),
             *
             *   - a self-join on forms (for the entry's primary
             *     form),
             *
             *   - 2 separate joins on native_spellings (for each
             *     form + the entry primary form),
             */
            $formsQ = DB::connection(config('tollerus.connection'))
                ->table('forms')
                ->join('lexemes as lx', 'lx.id', '=', 'forms.lexeme_id')
                ->join('entries as e', 'e.id', '=', 'lx.entry_id')
                ->join('languages as lang', 'lang.id', '=', 'e.language_id')
                ->leftJoin('neographies as pn', 'pn.id', '=', 'lang.primary_neography')
                // entry header primary form
                ->leftJoin('forms as pf', 'pf.id', '=', 'e.primary_form')
                // native spelling for matched form in language's primary neography
                ->leftJoin('native_spellings as ns_f', function ($join) {
                    $join->on('ns_f.form_id', '=', 'forms.id')
                        ->on('ns_f.neography_id', '=', 'lang.primary_neography');
                })
                // native spelling for entry primary form in language's primary neography
                ->leftJoin('native_spellings as ns_pf', function ($join) {
                    $join->on('ns_pf.form_id', '=', 'pf.id')
                        ->on('ns_pf.neography_id', '=', 'lang.primary_neography');
                })
                // transliterated search
                ->where('forms.transliterated', 'like', '%'.$key.'%');
            /**
             * Language lock (for page contexts where selecting a
             * different language is incoherent)
             */
            if ($this->language !== null) {
                $formsQ->where('e.language_id', $this->language->id);
            }
            /**
             * Soft particle filter (for convenience if the hopeful
             * `config('tollerus.particle_word_classes')` list is
             * adequate, but letting the user opt out if it's not)
             */
            if ($this->softLimitToParticles && count($this->particleClassIds) > 0) {
                $formsQ->whereIn('lx.word_class_id', $this->particleClassIds);
            }
            /**
             * This is where we define the final output structure.
             */
            $formsQ->select([
                'forms.id as form_id',
                'forms.global_id_raw as form_gid_raw',
                'forms.transliterated as form_transliterated',

                'e.id as entry_id',
                'e.global_id_raw as entry_gid_raw',
                'e.primary_form as entry_primary_form_id',

                'pf.transliterated as entry_transliterated', // null if primary_form is null
                'ns_pf.spelling as entry_native',            // null if no spelling / no primary neography
                'ns_f.spelling as form_native',              // null if no spelling / no primary neography

                'pn.machine_name as primary_neography_machine_name', // nullable
            ]);
            /**
             * Sort by relevance: exact / starts-with / contains
             */
            $formsQ
                ->selectRaw("
                    CASE
                        WHEN {$prefix}forms.transliterated = ? THEN 0
                        WHEN {$prefix}forms.transliterated LIKE ? THEN 1
                        WHEN {$prefix}forms.transliterated LIKE ? THEN 2
                        ELSE 3
                    END AS relevance
                ", [$key, $key.'%', '%'.$key.'%'])
                // Put best matches first within the scanned set
                ->orderBy('relevance')
                ->orderByRaw("CHAR_LENGTH({$prefix}forms.transliterated) ASC")
                ->orderBy('forms.transliterated')
                ->limit(self::MAX_ROWS_SCAN);
            /**
             * Run the forms-based query!
             */
            $formRows = $formsQ->get();
            // Add string length as a sorting tie-breaker
            $formRows = $formRows->map(function ($row) {
                $row->transliteratedLen = mb_strlen($row->form_transliterated);
                return $row;
            });
            /**
             * Right now, the entry info is stored in side properties on
             * each form, and the forms for a given entry may be
             * scattered.
             *
             * We need to group them and (eventually) re-sort the groups
             * based on the best-matching form inside each one.
             *
             * (Before we sort the groups though, we should merge them
             * with the glyphs. So the glyphs query will come first.)
             */
            $groups = $formRows->groupBy('entry_id')->map(fn ($g) => [
                'kind' => GlobalIdKind::Entry,
                'relevance' => $g->min('relevance'),
                'transliteratedLen' => $g->min('transliteratedLen'),
                /**
                 * Sort the forms within each entry the same way that
                 * the groups + glyphs will be sorted.
                 */
                'forms' => $g->sort(function ($a, $b) {
                    if ($a->relevance !== $b->relevance) {
                        return $a->relevance <=> $b->relevance;
                    }
                    if ($a->transliteratedLen !== $b->transliteratedLen) {
                        return $a->transliteratedLen <=> $b->transliteratedLen;
                    }
                    return 0;
                })->take(self::MAX_FORMS_PER_ENTRY)->values(),
            ])->take(self::MAX_ENTRIES)->values();

            /**
             * =======================================================
             *                      GLYPHS QUERY
             * =======================================================
             *
             * Before we add glyphs, we need to check if they're
             * applicable to the current config of this component.
             */
            if ($this->requireForm || ($this->softLimitToParticles && count($this->particleClassIds) > 0)) {
                // Not applicable, define as an empty set
                $glyphRows = collect([]);
                $glyphObjs = collect([]);
            } else {
                /**
                 * Glyphs are applicable, proceed with query ...
                 *
                 * This one is much simpler than the forms query because
                 * the only JOIN we need is neographies.
                 */
                $glyphsQ = DB::connection(config('tollerus.connection'))
                    ->table('neography_glyphs as g')
                    ->join('neographies as n', 'n.id', '=', 'g.neography_id')
                    ->where('g.transliterated', 'like', '%'.$key.'%')
                    ->orWhere('g.pronunciation_transliterated', 'like', '%'.$key.'%');
                /**
                 * Language lock (for page contexts where selecting a
                 * different language is incoherent)
                 */
                if ($this->language !== null) {
                    $glyphsQ->whereExists(function ($q) {
                        $q->selectRaw('1')
                            ->from('language_neography as ln')
                            ->whereColumn('ln.neography_id', 'g.neography_id')
                            ->where('ln.language_id', '=', $this->language->id);
                    });
                }
                /**
                 * Define output structure
                 */
                $glyphsQ->selectRaw("
                    {$prefix}g.id as glyph_id,
                    {$prefix}g.global_id_raw as glyph_gid_raw,
                    CASE
                        WHEN NULLIF({$prefix}g.transliterated,'') IS NOT NULL THEN {$prefix}g.transliterated
                        ELSE {$prefix}g.pronunciation_transliterated
                    END AS glyph_transliterated,
                    CASE
                        WHEN NULLIF({$prefix}g.transliterated,'') IS NOT NULL THEN {$prefix}g.glyph
                        ELSE {$prefix}g.pronunciation_native
                    END AS glyph_native,
                    {$prefix}n.machine_name as neography_machine_name
                ");
                /**
                 * Sort by relevance: exact / starts-with / contains
                 */
                $glyphsQ
                    ->selectRaw("
                        CASE
                            WHEN COALESCE(NULLIF({$prefix}g.transliterated,''), {$prefix}g.pronunciation_transliterated) = ? THEN 0
                            WHEN COALESCE(NULLIF({$prefix}g.transliterated,''), {$prefix}g.pronunciation_transliterated) LIKE ? THEN 1
                            WHEN COALESCE(NULLIF({$prefix}g.transliterated,''), {$prefix}g.pronunciation_transliterated) LIKE ? THEN 2
                            ELSE 3
                        END AS relevance
                    ", [$key, $key.'%', '%'.$key.'%'])
                    // Put best matches first within the scanned set
                    ->orderBy('relevance')
                    ->limit(self::MAX_ROWS_SCAN);
                /**
                 * Run the glyphs-based query!
                 */
                $glyphRows = $glyphsQ->get();
                // Add string length as a sorting tie-breaker
                $glyphObjs = $glyphRows->map(function ($row) {
                    $row->transliteratedLen = mb_strlen($row->glyph_transliterated);
                    /**
                     * Package to mimic the form groups below so we can
                     * sort them all in together.
                     */
                    return [
                        'kind' => GlobalIdKind::Glyph,
                        'relevance' => $row->relevance,
                        'transliteratedLen' => $row->transliteratedLen,
                        'glyph' => $row,
                    ];
                })->values();
            }

            /**
             * ================================================
             *                FINAL RESULT SET
             * ================================================
             */
            $results = $groups->concat($glyphObjs)->sort(function ($a, $b) {
                if ($a['relevance'] !== $b['relevance']) {
                    return $a['relevance'] <=> $b['relevance'];
                }
                if ($a['transliteratedLen'] !== $b['transliteratedLen']) {
                    return $a['transliteratedLen'] <=> $b['transliteratedLen'];
                }
                return 0;
            })->values();
            /**
             * We need the global IDs in string form, which requires
             * hydrating the models.
             */
            $entryIds = $formRows->pluck('entry_id')->unique()->values();
            $formIds  = $formRows->pluck('form_id')->unique()->values();
            $glyphIds = $glyphRows->pluck('glyph_id')->unique()->values();
            // Define lookup arrays
            $entriesById = Entry::query()
                ->whereIn('id', $entryIds)
                ->get()
                ->keyBy('id');
            $formsById = Form::query()
                ->whereIn('id', $formIds)
                ->get()
                ->keyBy('id');
            $glyphsById = NeographyGlyph::query()
                ->whereIn('id', $glyphIds)
                ->get()
                ->keyBy('id');
            // Reshape data for output
            $this->results = $results->map(function ($result) use ($entriesById, $formsById, $glyphsById) {
                if ($result['kind'] == GlobalIdKind::Entry) {
                    $firstRow = $result['forms']->first();
                    $entry = $entriesById->get(
                        (int)$firstRow->entry_id
                    );
                    return collect([
                        [
                            /**
                             * Entry result object
                             */
                            'globalId' => $entry->global_id,
                            'kind' => GlobalIdKind::Entry,
                            'neographyMachineName' => $firstRow->primary_neography_machine_name,
                            'transliterated' => $firstRow->entry_transliterated,
                            'native' => $firstRow->entry_native,
                        ],
                    ])->concat($result['forms']->map(function ($formRow) use ($formsById) {
                        $form = $formsById->get((int)$formRow->form_id);
                        return [
                            /**
                             * Form result object
                             */
                            'globalId' => $form->global_id,
                            'kind' => GlobalIdKind::Form,
                            'neographyMachineName' => $formRow->primary_neography_machine_name,
                            'transliterated' => $formRow->form_transliterated,
                            'native' => $formRow->form_native,
                        ];
                    }));
                } else {
                    $glyph = $glyphsById->get((int)$result['glyph']->glyph_id);
                    return collect([
                        [
                            /**
                             * Glyph result object
                             */
                            'globalId' => $glyph->global_id,
                            'kind' => GlobalIdKind::Glyph,
                            'neographyMachineName' => $result['glyph']->neography_machine_name,
                            'transliterated' => $result['glyph']->glyph_transliterated,
                            'native' => $result['glyph']->glyph_native,
                        ],
                    ]);
                }
            })->flatten(1)->values()->toArray();
        }
    }
}
