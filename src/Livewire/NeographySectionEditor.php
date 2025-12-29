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
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographyGlyph;
use PeterMarkley\Tollerus\Models\NeographyGlyphGroup;
use PeterMarkley\Tollerus\Models\NeographySection;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class NeographySectionEditor extends Component
{
    use HasModelCache;
    private $cacheRoot = 'groups';
    // Models
    #[Locked] public Neography $neography;
    #[Locked] public NeographySection $sect;
    #[Locked] public array $groups;
    // UI input layer
    public array $infoForm = [];
    public array $groupsForm = [];
    // UI display properties
    #[Locked] public array $glyphTypes = [];
    #[Locked] public array $sectTypes = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.neography-section-editor', [
                'glyphTypes' => $this->glyphTypes,
                'sectTypes' => $this->sectTypes,
            ])->layout('tollerus::components.layout', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.neographies.index'), 'text' => __('tollerus::ui.neographies')],
                    ['href' => route('tollerus.admin.neographies.edit.tab', ['neography' => $this->neography, 'tab' => 'glyphs']), 'text' => $this->neography->name],
                ],
            ])->title($this->sect->name);
    }
    public function mount(Neography $neography, NeographySection $sect): void
    {
        $this->neography = $neography;
        $this->sect = $sect;

        $this->glyphTypes = collect(NeographyGlyphType::cases())
            ->mapWithKeys(function ($type) {
                $typeStr = $type->value;
                return [$typeStr => [
                    'string' => $typeStr,
                    'enum' => $type,
                    'local' => $type->localize(),
                ]];
            })->toArray();
        $this->sectTypes = collect(NeographySectionType::cases())
            ->mapWithKeys(function ($type) {
                $typeStr = $type->value;
                return [$typeStr => [
                    'string' => $typeStr,
                    'enum' => $type,
                    'local' => $type->localize(),
                ]];
            })->toArray();

        $this->refreshForm();
    }

    /**
     * Refresh function
     */
    public function refreshForm(): void
    {
        $this->sect->loadMissing([
            'glyphGroups.glyphs'
        ]);
        $this->groups = $this->sect->glyphGroups->sortBy('position')->all();
        $this->infoForm = [
            'type' => ($this->sect->type === null ? null : $this->sect->type->value),
            'name' => $this->sect->name,
            'intro' => $this->sect->intro,
        ];
        $this->groupsForm = collect($this->groups)->mapWithKeys(function ($group) {
            return [$group->id => [
                'type' => ($group->type === null ? null : $group->type->value),
                'position' => $group->position,
                'glyphs' => $group->glyphs
                    ->sortBy('position')
                    ->mapWithKeys(function ($glyph) {
                        return [$glyph->id => [
                            'global_id'      => $glyph->global_id,
                            'position'       => $glyph->position,
                            'render_base'    => (bool)($glyph->render_base),
                            'glyph'          => $glyph->glyph,
                            'transliterated' => $glyph->transliterated,
                            'phonemic'       => $glyph->phonemic,
                            'pronunciation_transliterated' => $glyph->pronunciation_transliterated,
                            'pronunciation_phonemic'       => $glyph->pronunciation_phonemic,
                            'pronunciation_native'         => $glyph->pronunciation_native,
                            'note' => $glyph->note,
                        ]];
                    })->toArray(),
            ]];
        })->toArray();
    }

    /**
     * Save function
     */
    public function infoSave(): void
    {
        try {
            // Validate
            // $this->validate([
            //     'infoForm.name' => [
            //         Rule::unique('PeterMarkley\Tollerus\Models\NeographySection', 'name')->ignore($this->sect->id),
            //     ],
            // ]);
            // Save to database
            $this->sect->intro = $this->infoForm['intro'];
            $this->sect->save();
            // Refresh front-end state
            $this->refreshForm();
            $this->dispatch('save-info-success');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-info-failure');
            // Let error keep propagating
            throw $e;
        }
    }

    /**
     * Granular CRUD-type functions
     */
    public function updateSection(string $propName, string $propVal, ?string $domId = ''): void
    {
        // $propName whitelist
        $allowedPropData = [
            'type'  => ['type' => 'enum', 'enumClass' => NeographySectionType::class, 'column' => 'type'],
            'name'  => ['type' => 'string', 'column' => 'name'],
            // 'intro' => ['type' => 'string', 'column' => 'intro'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('sect-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'enum':
                if (empty($propVal)) {
                    $this->sect[$allowedPropData[$propName]['column']] = null;
                } else {
                    $enumInst = $allowedPropData[$propName]['enumClass']::from($propVal);
                    $this->sect[$allowedPropData[$propName]['column']] = $enumInst;
                }
            break;
            case 'string':
            default:
                $this->sect[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $this->sect->save();
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['infoForm.'.$propName => [__('tollerus::error.duplicate_of_unique_per_section')]]);
            } else {
                $this->dispatch('sect-update-failure');
                throw $e;
            }
        }
    }
    public function createGroup(): void
    {
        $nextPosition = collect($this->groups)->max('position') + 1;
        $this->sect->glyphGroups()->create([
            'position' => $nextPosition,
        ]);
        $this->refreshForm();
    }
    public function deleteGroup(string $groupId): void
    {
        NeographyGlyphGroup::findOrFail((int)$groupId)->delete();
        $this->refreshForm();
    }
    function swapGroups(string $groupId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($groupId, $neighborId) {
                $groupsCollection = collect($this->groups);
                $groupModel    = $groupsCollection->firstWhere('id', $groupId);
                $neighborModel = $groupsCollection->firstWhere('id', $neighborId);
                $oldGroupPosition    = (int) $this->groupsForm[$groupId]['position'];
                $oldNeighborPosition = (int) $this->groupsForm[$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $groupsCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $groupModel->position = $oldNeighborPosition;
                $groupModel->save();
                $neighborModel->position = $oldGroupPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('group-swap-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function createGlyph(string $groupId): void
    {
        // Find model
        $groupModel = $this->findInCache('glyph-add-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
            ],
        ]);
        // Create glyph
        $nextPosition = $groupModel->glyphs->max('position') + 1;
        $groupModel->glyphs()->create([
            'neography_id' => $this->neography->id,
            'position' => $nextPosition,
        ]);
        $this->refreshForm();
    }
    public function deleteGlyph(string $glyphId): void
    {
        NeographyGlyph::findOrFail((int)$glyphId)->delete();
        $this->refreshForm();
    }
    function swapGlyphs(string $groupId, string $glyphId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($groupId, $glyphId, $neighborId) {
                $groupModel = collect($this->groups)->firstWhere('id', $groupId);
                $glyphModel    = $groupModel->glyphs->firstWhere('id', $glyphId);
                $neighborModel = $groupModel->glyphs->firstWhere('id', $neighborId);
                $oldGlyphPosition    = (int) $this->groupsForm[$groupId]['glyphs'][$glyphId]['position'];
                $oldNeighborPosition = (int) $this->groupsForm[$groupId]['glyphs'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $groupModel->glyphs->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $glyphModel->position = $oldNeighborPosition;
                $glyphModel->save();
                $neighborModel->position = $oldGlyphPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('glyph-swap-failure');
            throw $e;
        }
        $this->refreshForm();
    }
}
