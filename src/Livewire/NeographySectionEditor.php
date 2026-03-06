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
use PeterMarkley\Tollerus\Domain\Neography\Services\NativeKeyboard;
use PeterMarkley\Tollerus\Domain\Neography\Services\PhonemicKeyboard;
use PeterMarkley\Tollerus\Enums\NeographyGlyphType;
use PeterMarkley\Tollerus\Enums\NeographySectionType;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
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
    #[Locked] public array $allSects = [];
    #[Locked] public array $nativeKeyboards = [];
    #[Locked] public array $ipaKeyboard = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.neography-section-editor')
            ->layout('tollerus::components.layouts.admin', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.neographies.index'), 'text' => __('tollerus::ui.neographies')],
                    ['href' => route('tollerus.admin.neographies.edit.tab', ['neography' => $this->neography, 'tab' => 'glyphs']), 'text' => $this->neography->name],
                ],
            ])->title($this->sect->name);
    }
    public function mount(Neography $neography, NeographySection $section): void
    {
        $this->neography = $neography;
        $this->sect = $section;

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

        /**
         * The virtual keyboards on this page are partly for
         * typing in the WYSIWYG, where there's no reason to restrict
         * the user to this neography. Hence we do `loadAll()`
         * instead of `loadForNeography()`.
         */
        $this->nativeKeyboards = app(NativeKeyboard::class)->loadAll();
        $this->ipaKeyboard = app(PhonemicKeyboard::class)->load();

        $this->refreshForm();
    }

    /**
     * Refresh function
     */
    public function refreshForm(): void
    {
        $this->sect->load([
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
                        $glyphLen = mb_strlen($glyph->glyph, 'UTF-8');
                        $glyphChars = [];
                        for ($i=0; $i < $glyphLen; $i++) {
                            $glyphChars[] = dechex(mb_ord(mb_substr($glyph->glyph, $i, 1, 'UTF-8'), 'UTF-8'));
                        }
                        $glyphHex = implode(', ', $glyphChars);
                        return [$glyph->id => [
                            'globalId'       => $glyph->global_id,
                            'position'       => $glyph->position,
                            'renderBase'     => (bool)($glyph->render_base),
                            'glyph'          => $glyph->glyph,
                            'glyphHex'       => $glyphHex,
                            'transliterated' => $glyph->transliterated,
                            'phonemic'       => $glyph->phonemic,
                            'pronunciationTransliterated' => $glyph->pronunciation_transliterated,
                            'pronunciationPhonemic'       => $glyph->pronunciation_phonemic,
                            'pronunciationNative'         => $glyph->pronunciation_native,
                            'note' => $glyph->note,
                        ]];
                    })->toArray(),
            ]];
        })->toArray();

        /**
         * We're not using `loadMissing()` here because we
         * actually need a guaranteed-fresh set.
         */
        $this->neography->load([
            'sections.glyphGroups.glyphs'
        ]);
        $this->allSects = $this->neography->sections->sortBy('position')
            ->map(function ($sect) {
                return [
                    'name' => $sect->name,
                    'id' => $sect->id,
                    'isThis' => $sect->id == $this->sect->id,
                    'groups' => $sect->glyphGroups->sortBy('position')
                        ->map(function ($group) {
                            return [
                                'id' => $group->id,
                                'glyphs' => $group->glyphs
                                    ->sortBy('position')
                                    ->pluck('glyph')
                                    ->toArray(),
                            ];
                        })->toArray(),
                ];
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
            $this->dispatch('text-save-success', id: $domId);
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
    public function updateGroup(string $groupId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $groupModel = $this->findInCache('group-update-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'type'  => ['type' => 'enum', 'enumClass' => NeographyGlyphType::class, 'column' => 'type'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('group-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'enum':
                if (empty($propVal)) {
                    $groupModel[$allowedPropData[$propName]['column']] = null;
                } else {
                    $enumInst = $allowedPropData[$propName]['enumClass']::from($propVal);
                    $groupModel[$allowedPropData[$propName]['column']] = $enumInst;
                }
            break;
        }
        // Save to database
        try {
            $groupModel->save();
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            $this->dispatch('group-update-failure');
            throw $e;
        }
    }
    public function deleteGroup(string $groupId): void
    {
        NeographyGlyphGroup::findOrFail((int)$groupId)->delete();
        $this->refreshForm();
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function swapGroups(string $groupId, string $neighborId): void
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
    public function updateGlyph(string $groupId, string $glyphId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $glyphModel = $this->findInCache('glyph-update-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
                'relation' => 'glyphs',
            ],
            [
                'id' => $glyphId,
                'objectType' => NeographyGlyph::class,
                'failMessage' => ['glyphId' => [__('tollerus::error.invalid_glyph')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'renderBase'     => ['type' => 'boolean', 'column' => 'render_base'],
            'glyph'          => ['type' => 'string', 'column' => 'glyph'],
            'glyphHex'       => ['type' => 'hex', 'column' => 'glyph'],
            'transliterated' => ['type' => 'string', 'column' => 'transliterated'],
            'phonemic'       => ['type' => 'string', 'column' => 'phonemic'],
            'pronunciationTransliterated' => ['type' => 'string', 'column' => 'pronunciation_transliterated'],
            'pronunciationPhonemic'       => ['type' => 'string', 'column' => 'pronunciation_phonemic'],
            'pronunciationNative'         => ['type' => 'string', 'column' => 'pronunciation_native'],
            'note' => ['type' => 'string', 'column' => 'note'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('glyph-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $glyphModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'hex':
                $valClean = str_replace(' ', '', $propVal);
                $valChars = explode(',', $valClean);
                $glyphChars = '';
                foreach ($valChars as $char) {
                    $glyphChars .= mb_chr(hexdec($char));
                }
                $glyphModel[$allowedPropData[$propName]['column']] = $glyphChars;
            break;
            case 'string':
            default:
                $glyphModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $glyphModel->save();
            $this->refreshForm(); // This is needed because 'glyph' and 'glyphHex' both access the same DB column
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['glyph.'.$propName => [__('tollerus::error.duplicate_of_glyph')]]);
            } else {
                $this->dispatch('glyph-update-failure');
                throw $e;
            }
        }
    }
    public function deleteGlyph(string $glyphId): void
    {
        NeographyGlyph::findOrFail((int)$glyphId)->delete();
        $this->refreshForm();
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function swapGlyphs(string $groupId, string $glyphId, string $neighborId): void
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
    public function transferGroup(string $groupId, string $destSect): void
    {
        // Basic sanity check
        if ($this->sect->id == $destSect) {
            $this->dispatch('group-transfer-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['destSect' => [__('tollerus::error.invalid_neography_section')]]);
        }
        // Find models
        $sectModel = $this->sect;
        $groupModel = $this->findInCache('group-transfer-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
            ],
        ]);
        // Transfer group
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($sectModel, $groupId, $groupModel, $destSect) {
                /**
                 * These aren't necessarily in our fancy model cache
                 */
                $destSectModel = $this->neography->sections->firstWhere('id', (int)$destSect);
                if (!($destSectModel instanceof NeographySection)) {
                    $this->dispatch('group-transfer-failure');
                    throw \Illuminate\Validation\ValidationException::withMessages(['destSect' => [__('tollerus::error.invalid_neography_section')]]);
                }
                /**
                 * To safely transplant the group, we have to make sure that during
                 * the transition its 'position' property doesn't conflict with any
                 * in EITHER section.
                 */
                $minPosition = min(
                    $sectModel->glyphGroups->min('position'),
                    $destSectModel->glyphGroups->min('position')
                );
                $nextPosition = $destSectModel->glyphGroups->max('position') + 1;
                // Move to universally safe position
                $groupModel->position = $minPosition - 1;
                $groupModel->save();
                // Transplant to destination section
                $groupModel->section_id = $destSectModel->id;
                $groupModel->save();
                // Move to final position in new section
                $groupModel->position = $nextPosition;
                $groupModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('group-transfer-failure');
            throw $e;
        }
        $this->refreshForm();
    }
    public function transferGlyph(string $groupId, string $glyphId, string $destSect, string $destGroup): void
    {
        // Basic sanity check
        if ($groupId == $destGroup) {
            $this->dispatch('glyph-transfer-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['destGroup' => [__('tollerus::error.invalid_glyph_group')]]);
        }
        // Find models
        $groupModel = $this->findInCache('glyph-transfer-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
            ],
        ]);
        $glyphModel = $this->findInCache('glyph-transfer-failure', [
            [
                'id' => $groupId,
                'objectType' => NeographyGlyphGroup::class,
                'failMessage' => ['groupId' => [__('tollerus::error.invalid_glyph_group')]],
                'relation' => 'glyphs',
            ],
            [
                'id' => $glyphId,
                'objectType' => NeographyGlyph::class,
                'failMessage' => ['glyphId' => [__('tollerus::error.invalid_glyph')]],
            ],
        ]);
        // Transfer glyph
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($groupId, $groupModel, $glyphId, $glyphModel, $destSect, $destGroup) {
                /**
                 * These aren't necessarily in our fancy model cache
                 */
                $destSectModel = $this->neography->sections->firstWhere('id', (int)$destSect);
                if (!($destSectModel instanceof NeographySection)) {
                    $this->dispatch('glyph-transfer-failure');
                    throw \Illuminate\Validation\ValidationException::withMessages(['destSect' => [__('tollerus::error.invalid_neography_section')]]);
                }
                $destGroupModel = $destSectModel->glyphGroups->firstWhere('id', (int)$destGroup);
                if (!($destGroupModel instanceof NeographyGlyphGroup)) {
                    $this->dispatch('glyph-transfer-failure');
                    throw \Illuminate\Validation\ValidationException::withMessages(['destGroup' => [__('tollerus::error.invalid_glyph_group')]]);
                }
                /**
                 * To safely transplant the glyph, we have to make sure that during
                 * the transition its 'position' property doesn't conflict with any
                 * in EITHER group.
                 */
                $minPosition = min(
                    $groupModel->glyphs->min('position'),
                    $destGroupModel->glyphs->min('position')
                );
                $nextPosition = $destGroupModel->glyphs->max('position') + 1;
                // Move to universally safe position
                $glyphModel->position = $minPosition - 1;
                $glyphModel->save();
                // Transplant to destination group
                $glyphModel->group_id = $destGroupModel->id;
                $glyphModel->save();
                // Move to final position in new group
                $glyphModel->position = $nextPosition;
                $glyphModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('glyph-transfer-failure');
            throw $e;
        }
        $this->refreshForm();
    }
}
