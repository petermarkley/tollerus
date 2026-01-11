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
use PeterMarkley\Tollerus\Models\Neography;
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
        $this->entry->loadMissing([
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
        //
    }
    public function deleteLexeme(string $lexemeId): void
    {
        //
    }
    public function swapLexemes(string $lexemeId, string $neighborId): void
    {
        //
    }
}
