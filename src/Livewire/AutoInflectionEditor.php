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
    private $cacheRoot = 'rules';
    public string $tabTarget = 'base';
    public string $tabPattern = 'transliterated';
    // Models
    #[Locked] public Language $language;
    #[Locked] public WordClassGroup $group;
    #[Locked] public InflectionTableRow $row;
    #[Locked] public array $rules;
    // UI input layer
    public array $ruleForm = [];

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
        $this->refreshRuleForm();
    }

    /**
     * Refresh UI input layer from database
     */
    public function refreshRuleForm(): void
    {
        $this->rules = $this->row->morphRules->sortBy([
            ['input_slot', 'asc'],
            ['order', 'asc']
        ])->all();
        $this->row->loadMissing([
            'sourceParticle.nativeSpellings',
        ]);
        $primaryNeographyId = $this->language->primary_neography;
        $rulesCollection = collect($this->rules);
        $this->ruleForm = [
            'row' => [
                'morphTemplate' => $this->row->morph_template,
                'srcParticle' => ($this->row->sourceParticle === null ? null : [
                    'id' => $this->row->sourceParticle->id,
                    'transliterated' => $this->row->sourceParticle->transliterated,
                    'phonemic' => $this->row->sourceParticle->phonemic,
                    'primaryNativeSpelling' => ($primaryNeographyId === null ? null : $this->row
                        ->sourceParticle
                        ->nativeSpellings
                        ->firstWhere('neography_id', $primaryNeographyId)
                        ->spelling
                    ),
                ]),
            ],
            'rules' => [
                'onBaseTrans' => $rulesCollection
                    ->filter(fn ($r) => (
                        $r->target_type == MorphRuleTargetType::BaseInput &&
                        $r->pattern_type == MorphRulePatternType::Transliterated
                    ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                        $rule->id => [
                            'pattern' => $rule->pattern,
                            'replacement' => $rule->replacement,
                            'order' => $rule->order,
                        ]
                    ])->toArray(),
                'onBasePhon' => $rulesCollection
                    ->filter(fn ($r) => (
                        $r->target_type == MorphRuleTargetType::BaseInput &&
                        $r->pattern_type == MorphRulePatternType::Phonemic
                    ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                        $rule->id => [
                            'pattern' => $rule->pattern,
                            'replacement' => $rule->replacement,
                            'order' => $rule->order,
                        ]
                    ])->toArray(),
                'onBaseNat' => $this->language->neographies->mapWithKeys(fn ($neography) => [
                    $neography->id => [
                        'neographyId' => $neography->id,
                        'rules' => $rulesCollection
                            ->filter(fn ($r) => (
                                $r->target_type == MorphRuleTargetType::BaseInput &&
                                $r->pattern_type == MorphRulePatternType::Native &&
                                $r->neography_id == $neography->id
                            ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                                $rule->id => [
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                    'order' => $rule->order,
                                ]
                            ])->toArray(),
                    ]
                ])->toArray(),
                'onParticleTrans' => $rulesCollection
                    ->filter(fn ($r) => (
                        $r->target_type == MorphRuleTargetType::ParticleInput &&
                        $r->pattern_type == MorphRulePatternType::Transliterated
                    ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                        $rule->id => [
                            'pattern' => $rule->pattern,
                            'replacement' => $rule->replacement,
                            'order' => $rule->order,
                        ]
                    ])->toArray(),
                'onParticlePhon' => $rulesCollection
                    ->filter(fn ($r) => (
                        $r->target_type == MorphRuleTargetType::ParticleInput &&
                        $r->pattern_type == MorphRulePatternType::Phonemic
                    ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                        $rule->id => [
                            'pattern' => $rule->pattern,
                            'replacement' => $rule->replacement,
                            'order' => $rule->order,
                        ]
                    ])->toArray(),
                'onParticleNat' => $this->language->neographies->mapWithKeys(fn ($neography) => [
                    $neography->id => [
                        'neographyId' => $neography->id,
                        'rules' => $rulesCollection
                            ->filter(fn ($r) => (
                                $r->target_type == MorphRuleTargetType::ParticleInput &&
                                $r->pattern_type == MorphRulePatternType::Native &&
                                $r->neography_id == $neography->id
                            ))->sortBy('order')->mapWithKeys(fn ($rule) => [
                                $rule->id => [
                                    'pattern' => $rule->pattern,
                                    'replacement' => $rule->replacement,
                                    'order' => $rule->order,
                                ]
                            ])->toArray(),
                    ]
                ])->toArray(),
            ],
        ];
        // dd($this->ruleForm);
    }

    /**
     * Granular UI functions
     */
}
