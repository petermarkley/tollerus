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
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\InflectionTable;
use PeterMarkley\Tollerus\Models\InflectionTableRow;
use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\MorphRule;
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
    public string $tabNeography = '';
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
        $this->language->loadMissing([
            'neographies',
        ]);
        $this->group = $group;
        $this->row = $row;
        $this->tabNeography = (string)$this->language->primary_neography;
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
            'sourceBase',
            'sourceParticle.nativeSpellings',
        ]);
        $primaryNeographyId = $this->language->primary_neography;
        $rulesCollection = collect($this->rules);
        $this->ruleForm = [
            'row' => [
                'morphTemplate' => $this->row->morph_template,
                'srcParticle' => ($this->row->sourceParticle === null ? ['id' => null, 'globalId' => ''] : [
                    'id' => (string)$this->row->sourceParticle->id,
                    'globalId' => $this->row->sourceParticle->global_id,
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
                'base' => [
                    'transliterated' => $rulesCollection
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
                    'phonemic' => $rulesCollection
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
                    'native' => $this->language->neographies->mapWithKeys(fn ($neography) => [
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
                ],
                'particle' => [
                    'transliterated' => $rulesCollection
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
                    'phonemic' => $rulesCollection
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
                    'native' => $this->language->neographies->mapWithKeys(fn ($neography) => [
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
            ],
        ];
        // dd($this->ruleForm);
    }

    /**
     * Granular UI functions
     */
    public function updateRow(string $propName, string $propVal, ?string $domId = ''): void
    {
        // $propName whitelist
        $allowedPropData = [
            'srcParticle'   => ['type' => 'int', 'column' => 'src_particle'],
            'morphTemplate' => ['type' => 'string', 'column' => 'morph_template'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('row-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately
        if ($propName == 'srcParticle') {
            try {
                $srcParticle = GlobalId::resolveId($propVal);
                $this->row->src_particle = $srcParticle->id;
            } catch (\Throwable $e) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['ruleForm.row.srcParticle.globalId' => [__('tollerus::error.invalid_src_particle')]]);
            }
        } else {
            $this->row[$allowedPropData[$propName]['column']] = $propVal;
        }
        // Save to database
        try {
            $this->row->save();
        } catch (\Throwable $e) {
            $this->dispatch('row-update-failure');
            throw $e;
        }
    }
    public function createRule(string $tabTarget, string $tabPattern, ?string $tabNeography = ''): void
    {
        try {
            // Get context
            $targetType = MorphRuleTargetType::from($tabTarget . '_input');
            $patternType = MorphRulePatternType::from($tabPattern);
            $neographyId = (empty($tabNeography) ? null : (int)$tabNeography);
            $rulesCollection = $this->getRulesCollection($targetType, $patternType, $neographyId);
            $nextPosition = $rulesCollection->max('order') + 1;
            // Create DB row
            $rule = $this->row->morphRules()->create([
                'pattern' => '',
                'replacement' => '',
                'target_type' => $targetType,
                'pattern_type' => $patternType,
                'neography_id' => $neographyId,
                'order' => $nextPosition,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('rule-add-failure');
            throw $e;
        }
        $this->refreshRuleForm();
    }
    public function deleteRule(string $ruleId): void
    {
        MorphRule::findOrFail((int)$ruleId)->delete();
        $this->refreshRuleForm();
    }
    function swapRules(string $tabTarget, string $tabPattern, string $tabNeography, string $ruleId, string $neighborId): void
    {
        try {
            // Get context
            $targetType = MorphRuleTargetType::from($tabTarget . '_input');
            $patternType = MorphRulePatternType::from($tabPattern);
            $neographyId = (empty($tabNeography) ? null : (int)$tabNeography);
            $rulesCollection = $this->getRulesCollection($targetType, $patternType, $neographyId);
            if ($patternType == MorphRulePatternType::Native) {
                $formArray = $this->ruleForm['rules'][$tabTarget][$tabPattern][(string)$tabNeography]['rules'];
            } else {
                $formArray = $this->ruleForm['rules'][$tabTarget][$tabPattern];
            }
            // Start DB transaction
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($rulesCollection, $formArray, $ruleId, $neighborId) {
                $ruleModel     = $rulesCollection->firstWhere('id', $ruleId);
                $neighborModel = $rulesCollection->firstWhere('id', $neighborId);
                $oldRulePosition     = (int) $formArray[$ruleId]['order'];
                $oldNeighborPosition = (int) $formArray[$neighborId]['order'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $rulesCollection->min('order');
                $neighborModel->order = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $ruleModel->order = $oldNeighborPosition;
                $ruleModel->save();
                $neighborModel->order = $oldRulePosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('rule-swap-failure');
            throw $e;
        }
        $this->refreshRuleForm();
    }

    /**
     * Utility functions
     */
    private function getRulesCollection(MorphRuleTargetType $targetType, MorphRulePatternType $patternType, int|null $neographyId): Collection
    {
        return collect($this->rules)->filter(fn ($r) => (
            $r->target_type == $targetType &&
            $r->pattern_type == $patternType &&
            $r->neography_id == $neographyId
        ));
    }
}
