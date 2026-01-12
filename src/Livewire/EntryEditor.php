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
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Lexeme;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\WordClass;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class EntryEditor extends Component
{
    // use HasModelCache;
    // private $cacheRoot = 'wordClassGroups';
    // Models
    #[Locked] public Language $language;
    #[Locked] public Entry $entry;
    // UI input layer
    public array $infoForm = [];
    // UI display properties
    #[Locked] public array $wordClassGroups = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        $neographyId = $this->language->primaryNeography?->id;
        return view('tollerus::livewire.entry-editor')
            ->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'entries',
                    ]), 'text' => $this->language->name],
                ],
            ])->title(mb_ucfirst($this->entry->primaryForm->transliterated));
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
        $this->language->loadMissing(['wordClasses']);
        $this->entry->load([
            'lexemes.wordClass',
            'lexemes.forms.nativeSpellings',
            'lexemes.senses.subsenses',
        ]);
        $this->infoForm = [
            'etym' => $this->entry->etym,
            'lexemes' => $this->entry->lexemes->mapWithKeys(function ($lexeme) {
                return [$lexeme->id => [
                    'wordClassId' => $lexeme->wordClass->id,
                    'wordClassName' => $lexeme->wordClass->name,
                    'position' => $lexeme->position,
                ]];
            }),
        ];
        $this->language->loadMissing([
            'wordClassGroups.wordClasses',
            'wordClassGroups.primaryClass',
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
            ];
        })->toArray();
    }
    public function infoSave(string $afterSuccess = '', array $payload = []): void
    {
        try {
            // // Validate
            // $this->validate([
            //     'infoForm.name' => [
            //         Rule::unique('PeterMarkley\Tollerus\Models\Language', 'name')->ignore($this->language->id),
            //     ],
            //     'infoForm.machine_name' => [
            //         'alpha_dash:ascii',
            //         Rule::unique('PeterMarkley\Tollerus\Models\Language', 'machine_name')->ignore($this->language->id),
            //     ],
            // ]);
            // // Save to database
            // $this->language->fill($this->infoForm);
            // $this->language->save();
            // Refresh front-end state
            $this->refreshForm();
            $this->dispatch('save-info-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-info-failure');
            // Let error keep propagating
            throw $e;
        }
    }

    /**
     * Granular CRUD-type functions
     */
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
        if ($this->entry->lexemes->pluck('wordClass')->contains($wordClassModel)) {
            $this->dispatch('lexeme-add-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['wordClassId' => [__('tollerus::error.dupliacte_of_unique_per_entry')]]);
        }

        // Create lexeme
        $nextPosition = $this->entry->lexemes->max('position') + 1;
        $lexeme = $this->entry->lexemes()->create([
            'language_id' => $this->language->id,
            'word_class_id' => $wordClassModel->id,
            'position' => $nextPosition,
        ]);

        $this->refreshForm();
    }
    public function deleteLexeme(string $lexemeId): void
    {
        Lexeme::findOrFail((int)$lexemeId)->delete();
        $this->refreshForm();
    }
    public function swapLexemes(string $lexemeId, string $neighborId): void
    {
        //
    }
}
