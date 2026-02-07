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

class PublicDictionary extends Component
{
    // use WithPagination, WithoutUrlPagination;
    // Models
    // #[Locked] public array $languages = [];
    // UI input layer
    // public string $sortBy = 'transliterated';
    // public string $searchStr = '';
    // public string $searchType = 'transliterated';

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.public-dictionary', [
                // 'paginator' => $paginator,
                // 'hasEntries' => $hasEntries,
            ])->layout('tollerus::components.layouts.public')
            ->title('My test page');
    }
    public function mount(): void
    {
        //
    }

    public function search()
    {
        $this->resetPage();
    }
}
