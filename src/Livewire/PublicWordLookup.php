<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Enums\SearchType;
use PeterMarkley\Tollerus\Models\Language;

class PublicWordLookup extends Component
{
    // use WithPagination, WithoutUrlPagination;
    // Models
    #[Locked] public Collection $languages;
    // UI input layer
    // public string $sortBy = 'transliterated';
    // public string $searchStr = '';
    // public string $searchType = 'transliterated';

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' My test page';
        }

        return view('tollerus::livewire.public-word-lookup', [
                // 'paginator' => $paginator,
                // 'hasEntries' => $hasEntries,
            ])->layout('tollerus::components.layouts.public')
            ->title($pageTitle);
    }
    public function mount(): void
    {
        $this->languages = Language::where('visible', true)->get();
    }

    public function search()
    {
        $this->resetPage();
    }
}
