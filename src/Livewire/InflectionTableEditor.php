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
use PeterMarkley\Tollerus\Enums\MorphRuleTargetType;
use PeterMarkley\Tollerus\Enums\MorphRulePatternType;
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
        $pageTitle = $this->language->name . ': ' . mb_ucfirst($groupName) . ': ' . __('tollerus::ui.inflection_tables');
        return view('tollerus::livewire.inflection-table-editor', [
                'groupName' => $groupName,
                'pageTitle' => $pageTitle,
            ])->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.languages.index'), 'text' => __('tollerus::ui.languages')],
                    ['href' => route('tollerus.admin.languages.edit.tab', ['language' => $this->language->id, 'tab' => 'grammar']), 'text' => $this->language->name],
                ],
            ])->title($pageTitle);
    }
    public function mount(Language $language, WordClassGroup $group): void
    {
        $this->language = $language;
        $this->group = $group;
        $this->tables = $group->inflectionTables->sortBy('position')->all();
        $this->refreshTableForm();
    }

    /**
     * Refresh UI input layer from database
     */
    public function refreshTableForm(): void
    {
        $this->group->loadMissing([
            'features.featureValues',
        ]);
        foreach ($this->tables as $table) {
            $table->loadMissing([
                'filterValues.feature',
                'rows.filterValues.feature',
                'rows.sourceParticle.nativeSpellings',
            ]);
        }
        $primaryNeographyId = $this->language->primary_neography;
        $neographies = $this->language->neographies;
        $this->tableForm = collect($this->tables)->mapWithKeys(function ($table) use ($primaryNeographyId, $neographies) {
            return [$table->id => [
                'label'        => $table->label,
                'visible'      => (bool)($table->visible),
                'showLabel'    => (bool)($table->show_label),
                'position'     => $table->position,
                'stack'        => (bool)($table->stack),
                'alignOnStack' => (bool)($table->align_on_stack),
                'tableFold'    => (bool)($table->table_fold),
                'rowsFold'     => (bool)($table->rows_fold),
                'filters' => $table->filterValues->mapWithKeys(function ($filterValue) {
                    return [$filterValue->id => [
                        'featureId'   => $filterValue->feature->id,
                        'featureName' => $filterValue->feature->name,
                        'valueId'     => $filterValue->id,
                        'valueName'   => $filterValue->name,
                    ]];
                })->toArray(),
                'rows' => $table->rows->sortBy('position')->mapWithKeys(function ($row) use ($primaryNeographyId, $neographies) {
                    return [$row->id => [
                        'label'         => $row->label,
                        'labelBrief'    => $row->label_brief,
                        'labelLong'     => $row->label_long,
                        'visible'       => (bool)($row->visible),
                        'showLabel'     => (bool)($row->show_label),
                        'position'      => $row->position,
                        'morphTemplate' => $row->morph_template,
                        'srcBase'       => $row->src_base,
                        'srcParticle' => ($row->sourceParticle === null ? null : [
                            'id' => $row->sourceParticle->id,
                            'transliterated' => $row->sourceParticle->transliterated,
                            'phonemic' => $row->sourceParticle->phonemic,
                            'primaryNativeSpelling' => $row->sourceParticle->nativeSpellings
                                ->firstWhere('neography_id', $primaryNeographyId),
                        ]),
                        'filters' => $row->filterValues->mapWithKeys(function ($filterValue) {
                            return [$filterValue->id => [
                                'featureId'   => $filterValue->feature->id,
                                'featureName' => $filterValue->feature->name,
                                'valueId'     => $filterValue->id,
                                'valueName'   => $filterValue->name,
                            ]];
                        })->toArray(),
                        'morphRules' => [
                            'onBaseTransliterated' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::BaseInput && $r->pattern_type == MorphRulePatternType::Transliterated)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onBasePhonemic' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::BaseInput && $r->pattern_type == MorphRulePatternType::Phonemic)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onBaseNative' => $neographies->mapWithKeys(fn ($neography) => [
                                $neography->id => [
                                    'neographyId' => $neography->id,
                                    'rules' => $row->morphRules->filter(
                                        fn ($r) => (
                                            $r->target_type == MorphRuleTargetType::BaseInput &&
                                            $r->pattern_type == MorphRulePatternType::Transliterated &&
                                            $r->neography_id == $neography->id
                                        )
                                    )->sortBy('order')->mapWithKeys(function ($rule) {
                                        return [$rule->id => [
                                            'order' => $rule->order,
                                            'pattern' => $rule->pattern,
                                            'replacement' => $rule->replacement,
                                        ]];
                                    })->toArray(),
                                ]
                            ])->toArray(),
                            'onParticleTransliterated' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::ParticleInput && $r->pattern_type == MorphRulePatternType::Transliterated)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onParticlePhonemic' => $row->morphRules->filter(
                                fn ($r) => ($r->target_type == MorphRuleTargetType::ParticleInput && $r->pattern_type == MorphRulePatternType::Phonemic)
                            )->sortBy('order')->mapWithKeys(function ($rule) {
                                return [$rule->id => [
                                    'order' => $rule->order,
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                ]];
                            })->toArray(),
                            'onParticleNative' => $neographies->mapWithKeys(fn ($neography) => [
                                $neography->id => [
                                    'neographyId' => $neography->id,
                                    'rules' => $row->morphRules->filter(
                                        fn ($r) => (
                                            $r->target_type == MorphRuleTargetType::ParticleInput &&
                                            $r->pattern_type == MorphRulePatternType::Transliterated &&
                                            $r->neography_id == $neography->id
                                        )
                                    )->sortBy('order')->mapWithKeys(function ($rule) {
                                        return [$rule->id => [
                                            'order' => $rule->order,
                                            'pattern' => $rule->pattern,
                                            'replacement' => $rule->replacement,
                                        ]];
                                    })->toArray(),
                                ]
                            ])->toArray(),
                        ],
                    ]];
                })->toArray(),
            ]];
        })->toArray();
    }

    /**
     * Granular UI functions
     */
    function moveTable(
        string $tableId,
        int $dir // -1 is up; +1 is down
    ): void
    {
        // normalize direction input
        if ($dir == 0) {
            return;
        }
        $dir = (int)($dir / abs($dir));
        // Prepare some numeric arrays
        $tableFormSorted = collect($this->tableForm)->sortBy('position');
        $positionsNumeric = $tableFormSorted->pluck('position')->values()->toArray();
        $idsNumeric = $tableFormSorted->keys()->toArray();
        // Find certain numeric indices
        $tableIndex = array_search($tableId, $idsNumeric);
        if ($tableIndex === false) {
            return;
        }
        $neighborIndex = $tableIndex + $dir;
        if ($neighborIndex < 0 || $neighborIndex >= count($idsNumeric)) {
            return;
        }
        // Now we can finally deduce the table ID for the neighbor
        $neighborTableId = $idsNumeric[$neighborIndex];
        // And perform the swap
        $storedPosition = $this->tableForm[$tableId]['position'];
        $this->tableForm[$tableId]['position'] = $this->tableForm[$neighborTableId]['position'];
        $this->tableForm[$neighborTableId]['position'] = $storedPosition;
        // FIXME - persist to DB
    }
}
