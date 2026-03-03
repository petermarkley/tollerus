<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        }
    }
}
