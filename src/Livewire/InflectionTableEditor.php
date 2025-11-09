<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;

class InflectionTableEditor extends Component
{
    // Models
    #[Locked] public Language $language;
    #[Locked] public WordClassGroup $group;
    #[Locked] public array $tables;

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        if ($this->group->primaryClass === null) {
            $groupName = __('tollerus::ui.group_nameless');
        } else {
            $groupName = $this->group->primaryClass->name;
        }
        $pageTitle = $this->language->name . ': ' . ucfirst($groupName) . ': ' . __('tollerus::ui.inflection_tables');
        return view('tollerus::livewire.inflection-table-editor', ['pageTitle' => $pageTitle])
            ->layout('tollerus::components.layout')
            ->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $group): void
    {
        $this->language = $language;
        $this->group = $group;
        $this->tables = $group->inflectionTables->all();
    }
}
