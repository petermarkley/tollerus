<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
use PeterMarkley\Tollerus\Models\Feature;
use PeterMarkley\Tollerus\Models\FeatureValue;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\InflectionTableRow;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\WordClassGroup;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableFilter;
use PeterMarkley\Tollerus\Models\Pivots\InflectionTableRowFilter;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class AutoInflectionEditor extends Component
{
    use HasModelCache;
    private $cacheRoot = 'tables';
    // Models
    #[Locked] public Language $language;
    #[Locked] public WordClassGroup $group;
    #[Locked] public InflectionTableRow $row;
    #[Locked] public array $tables;
    // UI input layer
    public array $tableForm = [];

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
        if (empty($this->row->label_brief)) {
            $rowName = $this->row->label;
        } else {
            $rowName = $this->row->label_brief;
        }
        $pageTitle = $this->language->name . ': ' . mb_ucfirst($rowName) . ': ' . __('tollerus::ui.auto_inflection');
        return view('tollerus::livewire.auto-inflection-editor', [
                'groupName' => $groupName,
                'rowName'   => $rowName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', [
                        'language' => $this->language->id,
                        'tab' => 'grammar',
                    ]), 'text' => $this->language->name],
                    ['href' => route('tollerus.admin.languages.inflection-tables', [
                        'language' => $this->language->id,
                        'group' => $this->group->id,
                    ]), 'text' => mb_ucfirst($groupName) . ' ' . __('tollerus::ui.inflection_tables')],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $group, InflectionTableRow $row): void
    {
        $this->language = $language;
        $this->group = $group;
        $this->row = $row;
        // $this->refreshTableForm();
    }

    /**
     * Refresh UI input layer from database
     */

    /**
     * Granular UI functions
     */
}
