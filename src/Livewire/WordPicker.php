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
    private ?Language $language = null;
    #[Locked] public array $particleClasses = [];
    public ?string $selectedWordId = null;
    private ?GlobalIdKind $selectedWordKind = null;
    private NeographyGlyph|Entry|Form|null $selectedWord = null;
    #[Locked] public string $selectedWordTransliterated = '';
    #[Locked] public string $selectedWordNative = '';
    private ?Neography $selectedWordNativeNeography = null;
    public string $searchKey = '';
    private array $results = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        //
        return view('tollerus::livewire.word-picker', [
            'language' => $this->language,
            'selectedWordKind' => $this->selectedWordKind,
            'selectedWord' => $this->selectedWord,
            'selectedWordNativeNeography' => $this->selectedWordNativeNeography,
            'results' => $this->results,
        ]);
    }
    public function mount(
        bool $softLimitToParticles = false,
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
     * Page interactions
     */
    public function deselectWord() {
        $this->selectedWordId = '';
        $this->selectedWordKind = null;
        $this->selectedWord = null;
        $this->selectedWordTransliterated = '';
        $this->selectedWordNative = '';
        $this->selectedWordNativeNeography = null;
    }
    public function selectWord(string $selectedWordId) {
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
        /**
         * Now we need make a best attempt at populating
         * the transliterated and native spellings.
         */
        switch ($this->selectedWordKind) {
            case GlobalIdKind::Glyph:
                $this->selectedWordNativeNeography = $obj->neography;
                if (!empty($obj->transliterated)) {
                    $this->selectedWordTransliterated = $obj->transliterated;
                    $this->selectedWordNative = $obj->glyph;
                } else if (!empty($obj->pronunciation_transliterated)) {
                    $this->selectedWordTransliterated = $obj->pronunciation_transliterated;
                    $this->selectedWordNative = $obj->pronunciation_native;
                } else {
                    $this->selectedWordTransliterated = '';
                    $this->selectedWordNative = '';
                }
            break;
            case GlobalIdKind::Entry:
                $this->selectedWordNativeNeography = $obj->language->primaryNeography;
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
                    $this->selectedWordTransliterated = $form->transliterated;
                    $nativeSpelling = $form->nativeSpellings()
                        ->where('neography_id', $this->selectedWordNativeNeography->id)
                        ->first();
                    if ($nativeSpelling instanceof NativeSpelling) {
                        $this->selectedWordNative = $nativeSpelling->spelling;
                    } else {
                        $this->selectedWordNative = '';
                    }
                } else {
                    // No form, we have to give up
                    $this->selectedWordTransliterated = '';
                    $this->selectedWordNative = '';
                }
            break;
            case GlobalIdKind::Form:
                $this->selectedWordNativeNeography = $obj->language->primaryNeography;
                $this->selectedWordTransliterated = $obj->transliterated;
                $nativeSpelling = $obj->nativeSpellings()
                    ->where('neography_id', $this->selectedWordNativeNeography->id)
                    ->first();
                if ($nativeSpelling instanceof NativeSpelling) {
                    $this->selectedWordNative = $nativeSpelling->spelling;
                } else {
                    $this->selectedWordNative = '';
                }
            break;
        }
    }
    public function refreshForm() {
        $this->searchKey = '';
        $this->results = [];
    }
    public function search() {
        //
    }
}
