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
    #[Locked] public bool $softLimitToParticles = false;
    #[Locked] public bool $requireForm = false;
    #[Locked] public ?Language $language = null;
    #[Locked] public array $particleClasses = [];
    public ?string $selectedWordId = null;
    #[Locked] public ?GlobalIdKind $selectedWordKind = null;
    #[Locked] public NeographyGlyph|Entry|Form|null $selectedWord = null;
    #[Locked] public string $selectedWordTransliterated = '';
    #[Locked] public string $selectedWordNative = '';
    #[Locked] public ?Neography $selectedWordNativeNeography = null;
    public string $searchKey = '';
    #[Locked] public array $results = [];

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
        $this->language = $language;
        $this->particleClasses = config('tollerus.particle_word_classes');
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
            /**
             * If yes, lexemes get special treatment. We will recognize them
             * but offer only related IDs to actually select.
             *
             * Glyphs, Entries, and Forms are all selectable directly.
             */
            $obj = $globalId->resolve();
            if ($globalId->kind == GlobalIdKind::Lexeme) {
                $entry = $obj->entry;
                $this->results[] = $this->buildWord($entry->global_id, GlobalIdKind::Entry, $entry);
                $forms = $obj->forms;
                foreach ($forms as $form) {
                    $this->results[] = $this->buildWord($form->global_id, GlobalIdKind::Form, $form);
                }
            } else {
                $this->results[] = $this->buildWord($this->searchKey, $globalId->kind, $obj);
            }
        } else {
            /**
             * Alright, we need to build a couple of monster queries here ...
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

            /**
             * Note 2026/03/03 by Peter Markley:
             *
             * The rest of this `else` branch is currently a draft by
             * ChatGPT, to be combed over and tested carefully very soon.
             * I already see a couple of gaps in desired functionality,
             * but it's a decent start. This is an ACTIVE WIP.
             */
            $key = trim((string) $this->searchKey);
            if ($key === '') {
                $this->results = [];
                return;
            }

            $rawConnection = DB::connection(config('tollerus.connection'));
            $prefix = $rawConnection->getTablePrefix();

            // Tweakable limits
            $maxEntries = 20;          // number of entry groups to display
            $maxFormsPerEntry = 6;     // number of forms shown under each entry
            $maxFormRowsScan = 250;    // how many matched forms we scan before grouping

            $exact = $key;
            $start = $key . '%';
            $like  = '%' . $key . '%';

            /**
             * =========================================================
             * 1) Query matched FORMS (transliterated search)
             *    Return enough info to build:
             *    - entry header (based on entries.primary_form only)
             *    - form rows under it
             *    - primary neography machine name (nullable)
             * =========================================================
             */
            $formsQ = DB::connection(config('tollerus.connection'))
                ->table('forms')
                ->join('lexemes as lx', 'lx.id', '=', 'forms.lexeme_id')
                ->join('entries as e', 'e.id', '=', 'lx.entry_id')
                ->join('languages as lang', 'lang.id', '=', 'e.language_id')
                ->leftJoin('neographies as pn', 'pn.id', '=', 'lang.primary_neography')

                // entry header primary form (may be null)
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
                ->where('forms.transliterated', 'like', $like);

            // Language lock: exclude glyphs by skipping glyph query entirely in this branch.
            if ($this->language !== null) {
                $formsQ->where('e.language_id', $this->language->id);
            }

            // Soft particle filter
            if ($this->softLimitToParticles) {
                $ids = $this->particleClassIds ?? [];
                if (!empty($ids)) {
                    $formsQ->whereIn('lx.word_class_id', $ids);
                }
            }

            // Simple relevance: exact, starts-with, contains
            $formsQ->select([
                'forms.id as form_id',
                'forms.global_id_raw as form_gid_raw',
                'forms.transliterated as form_transliterated',

                'e.id as entry_id',
                'e.global_id_raw as entry_gid_raw',
                'e.primary_form as entry_primary_form_id',

                'pf.transliterated as entry_transliterated',     // null if primary_form is null
                'ns_pf.spelling as entry_native',                // null if no spelling / no primary neography
                'ns_f.spelling as form_native',                  // null if no spelling / no primary neography

                'pn.machine_name as primary_neography_machine_name', // nullable
            ]);

            $formsQ
                ->selectRaw("
                    CASE
                    WHEN {$prefix}forms.transliterated = ? THEN 0
                    WHEN {$prefix}forms.transliterated LIKE ? THEN 1
                    WHEN {$prefix}forms.transliterated LIKE ? THEN 2
                    ELSE 3
                    END AS relevance
                ", [$exact, $start, $like])

                // Put best matches first *within the scanned set*
                ->orderBy('relevance')
                ->orderByRaw("CHAR_LENGTH({$prefix}forms.transliterated) ASC")
                ->orderBy('forms.transliterated')
                ->limit($maxFormRowsScan);

            $rows = $formsQ->get();

            /**
             * =========================================================
             * 2) Hydrate Entry & Form models for global_id accessor
             * =========================================================
             */
            $entryIds = $rows->pluck('entry_id')->unique()->values();
            $formIds  = $rows->pluck('form_id')->unique()->values();

            /** @var \Illuminate\Support\Collection<int, Entry> $entriesById */
            $entriesById = Entry::query()
                ->whereIn('id', $entryIds)
                ->get()
                ->keyBy('id');

            /** @var \Illuminate\Support\Collection<int, Form> $formsById */
            $formsById = Form::query()
                ->whereIn('id', $formIds)
                ->get()
                ->keyBy('id');

            /**
             * =========================================================
             * 3) Grouping + sorting (preserve groups)
             *
             * Strategy:
             * - Group rows by entry_id
             * - Each group gets a "group score" = best (min) relevance
             *   among its forms, then min form length (tie breaker)
             * - Sort groups by that score
             * - Within group: sort forms by relevance, then length
             * - Emit: entry row, then up to N form rows
             * =========================================================
             */
            $groups = $rows->groupBy('entry_id')->map(function ($groupRows) {
                $bestRel = $groupRows->min('relevance');
                $bestLen = $groupRows->min(fn ($r) => mb_strlen((string) $r->form_transliterated));
                return [
                    'rows' => $groupRows,
                    'bestRel' => $bestRel,
                    'bestLen' => $bestLen,
                ];
            });

            $groups = $groups->sort(function ($a, $b) {
                // primary: relevance
                if ($a['bestRel'] !== $b['bestRel']) return $a['bestRel'] <=> $b['bestRel'];
                // secondary: length
                if ($a['bestLen'] !== $b['bestLen']) return $a['bestLen'] <=> $b['bestLen'];
                return 0;
            });

            $results = [];

            foreach ($groups as $entryId => $g) {
                if (count($results) >= ($maxEntries * (1 + $maxFormsPerEntry))) {
                    break;
                }

                $entryModel = $entriesById->get((int) $entryId);
                if (!$entryModel) continue;

                // Pick a representative row (contains entry header fields already joined)
                $first = $g['rows']->first();

                // ENTRY HEADER (primary form only; if null, transliterated/native remain null/empty)
                $results[] = [
                    'globalId' => $entryModel->global_id, // accessor
                    'kind' => GlobalIdKind::Entry,
                    'neographyMachineName' => $first->primary_neography_machine_name ?? null,
                    'transliterated' => $first->entry_transliterated ?? null,
                    'native' => $first->entry_native ?? null,
                ];

                // FORM ROWS
                $formRows = $g['rows']
                    ->sort(function ($a, $b) {
                        if ($a->relevance !== $b->relevance) return $a->relevance <=> $b->relevance;
                        $la = mb_strlen((string) $a->form_transliterated);
                        $lb = mb_strlen((string) $b->form_transliterated);
                        if ($la !== $lb) return $la <=> $lb;
                        return strcmp((string) $a->form_transliterated, (string) $b->form_transliterated);
                    })
                    ->take($maxFormsPerEntry);

                foreach ($formRows as $r) {
                    $formModel = $formsById->get((int) $r->form_id);
                    if (!$formModel) continue;

                    $results[] = [
                        'globalId' => $formModel->global_id, // accessor
                        'kind' => GlobalIdKind::Form,
                        'neographyMachineName' => $r->primary_neography_machine_name ?? null,
                        'transliterated' => $r->form_transliterated ?? null,
                        'native' => $r->form_native ?? null,
                    ];
                }
            }

            /**
             * =========================================================
             * 4) (Optional) glyph search merge
             *
             * If language is null, you probably also want glyph hits.
             * Do it as a second query and then merge based on your chosen
             * display order rules.
             * =========================================================
             */
            $this->results = $results;
        }
    }
}
