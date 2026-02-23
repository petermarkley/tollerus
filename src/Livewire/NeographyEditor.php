<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Domain\Neography\Actions\GlyphsToKeyboard;
use PeterMarkley\Tollerus\Domain\Neography\Actions\SvgToGlyphs;
use PeterMarkley\Tollerus\Domain\Neography\Actions\SvgToKeyboard;
use PeterMarkley\Tollerus\Domain\Neography\Services\FontAssetService;
use PeterMarkley\Tollerus\Enums\FontFormat;
use PeterMarkley\Tollerus\Enums\WritingDirection;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\NeographySection;
use PeterMarkley\Tollerus\Models\NeographyInputKey;
use PeterMarkley\Tollerus\Models\NeographyInputKeyboard;
use PeterMarkley\Tollerus\Support\Markup\BodyTextRenderer;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class NeographyEditor extends Component
{
    use WithFileUploads;
    use HasModelCache;
    private $cacheRoot = 'keyboards';
    public string $tab = 'info';
    // Models
    #[Locked] public Neography $neography;
    #[Locked] public array $sects;
    #[Locked] public array $keyboards;
    // UI input layer
    public array $infoForm = [];
    public array $fontForm = [];
    public array $fontUploads = [];
    public array $glyphsForm = [];
    public array $keysForm = [];
    // UI display properties
    #[Locked] public array $writingDirectionOpts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.neography-editor')
            ->layout('tollerus::components.layouts.admin', [
                'breadcrumbs' => [
                    ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
                    ['href' => route('tollerus.admin.neographies.index'), 'text' => __('tollerus::ui.neographies')],
                ],
            ])->title($this->neography->name);
    }
    public function mount(Neography $neography, ?string $tab = null): void
    {
        $this->neography = $neography;
        $this->tab = $tab ?? 'info';

        // Info tab
        $this->refreshInfoForm();
        $this->writingDirectionOpts = collect(WritingDirection::cases())
            ->mapWithKeys(function ($direction) {
                $directionString = $direction->value;
                $secondaryDirectionOpts = $direction->axis()->perpendicular()->directions();
                $secondaryDirectionStrs = collect($secondaryDirectionOpts)
                    ->map(fn ($direction) => $direction->value)
                    ->toArray();
                return [$directionString => [
                    'string' => $directionString,
                    'enum' => $direction,
                    'local' => $direction->localize(),
                    'secondaryOpts' => $secondaryDirectionStrs,
                ]];
            })->toArray();

        // Font tab
        $this->refreshFontForm();

        // Glyphs tab
        $this->refreshGlyphsForm();

        // Keyboards tab
        $this->refreshKeyboardsForm();
    }
    public function updatedFontUploads(TemporaryUploadedFile $file, string $key): void
    {
        $fontFormat = FontFormat::from($key);
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $fontFormat->mimeTypes())) {
            throw \Illuminate\Validation\ValidationException::withMessages(["fontUploads.$key" => [__('tollerus::error.invalid_file_mime_type', ['mime_type' => $mimeType])]]);
        }
        $fileSize = $file->getSize();
        if ($fileSize > config('tollerus.max_font_size')) {
            throw \Illuminate\Validation\ValidationException::withMessages(["fontUploads.$key" => [__('tollerus::error.file_too_big', ['size' => $fileSize])]]);
        }
        $this->neography->{$fontFormat->blobColumn()} = $file->get();
        $this->neography->save();
        $this->fontUploads[$key] = null;
        $this->publishFont($fontFormat);
    }

    /**
     * Convenience functions that switch between methods based on tab
     */
    public function refreshForm(string $tab): void
    {
        switch ($tab) {
            case 'info':
                $this->refreshInfoForm();
            break;
            case 'font':
                $this->refreshFontForm();
            break;
            case 'glyphs':
                $this->refreshGlyphsForm();
            break;
            case 'keyboards':
                $this->refreshKeyboardsForm();
            break;
        }
    }
    public function save(string $tab, string $afterSuccess = '', array $payload = []): void
    {
        switch ($tab) {
            case 'info':
                $this->infoSave($afterSuccess, $payload);
            break;
            case 'font':
                $this->fontSave($afterSuccess, $payload);
            break;
        }
    }

    /**
     * Tab-specific refresh functions
     */
    public function refreshInfoForm(): void
    {
        $this->infoForm = [
            'name'                => $this->neography->name,
            'machine_name'        => $this->neography->machine_name,
            'direction_primary'   => $this->neography->direction_primary->value,
            'direction_secondary' => $this->neography->direction_secondary->value,
            'boustrophedon'       => (bool)($this->neography->boustrophedon),
            'visible'             => (bool)($this->neography->visible),
        ];
    }
    public function refreshFontForm(): void
    {
        $neography = $this->neography;
        $this->fontForm = [
            'formats' => collect(FontFormat::cases())->mapWithKeys(function ($fontFormat) use ($neography) {
                $path = $neography->{$fontFormat->pathColumn()};
                $url = $neography->{$fontFormat->urlColumn()};
                $published = (!empty($path) && !empty($url));
                $mimeTypes = $fontFormat->mimeTypes();
                if ($published && is_file($path) && in_array(mime_content_type($path), $mimeTypes)) {
                    $valid = true;
                } else {
                    $valid = false;
                }
                return [$fontFormat->value => [
                    'blobExists' => !empty($neography->{$fontFormat->blobColumn()}),
                    'published' => $published,
                    'url' => $url,
                    'valid' => $valid,
                ]];
            })->toArray(),
            'css' => $this->neography->font_css,
        ];
    }
    public function refreshGlyphsForm(): void
    {
        $neography = $this->neography;
        $this->neography->loadMissing([
            'sections.glyphGroups.glyphs'
        ]);
        $this->sects = $this->neography->sections->sortBy('position')->all();
        $this->glyphsForm = collect($this->sects)->mapWithKeys(function ($sect) use ($neography) {
            return [$sect->id => [
                'type' => ($sect->type === null ? null : $sect->type->value),
                'name' => $sect->name,
                'intro' => app(BodyTextRenderer::class)->render($sect->intro),
                'position' => $sect->position,
                'editUrl' => route('tollerus.admin.neographies.glyphs.edit', ['neography' => $neography, 'section' => $sect]),
                'editUrlText' => __('tollerus::ui.edit_thing', ['thing' => $sect->name]),
                'glyphCount' => $sect->glyphGroups->flatMap->glyphs->count(),
            ]];
        })->toArray();
    }
    public function refreshKeyboardsForm(): void
    {
        $this->keyboards = $this->neography->keyboards->sortBy('position')->all();
        $this->keysForm = collect($this->keyboards)->mapWithKeys(function ($keyboard) {
            return [$keyboard->id => [
                'position' => $keyboard->position,
                'width'    => (string)($keyboard->width),
                'keys'     => $keyboard->inputKeys
                    ->sortBy('position')
                    ->mapWithKeys(function ($key) {
                        $glyphLen = mb_strlen($key->glyph, 'UTF-8');
                        $glyphChars = [];
                        for ($i=0; $i < $glyphLen; $i++) {
                            $glyphChars[] = dechex(mb_ord(mb_substr($key->glyph, $i, 1, 'UTF-8'), 'UTF-8'));
                        }
                        $glyphHex = implode(', ', $glyphChars);
                        return [$key->id => [
                            'label'      => $key->label,
                            'glyph'      => $key->glyph,
                            'glyphHex'   => $glyphHex,
                            'position'   => $key->position,
                            'renderBase' => (bool)($key->render_base),
                        ]];
                    })->toArray(),
            ]];
        })->toArray();
    }

    /**
     * Tab-specific save functions
     */
    public function infoSave(string $afterSuccess = '', array $payload = []): void
    {
        try {
            // Validate
            $this->validate([
                'infoForm.name' => [
                    Rule::unique('PeterMarkley\Tollerus\Models\Neography', 'name')->ignore($this->neography->id),
                ],
                'infoForm.machine_name' => [
                    'alpha_dash:ascii',
                    Rule::unique('PeterMarkley\Tollerus\Models\Neography', 'machine_name')->ignore($this->neography->id),
                ],
                'infoForm.direction_primary' => [
                    Rule::enum(WritingDirection::class),
                ],
                'infoForm.direction_secondary' => [
                    Rule::enum(WritingDirection::class)->only(
                        WritingDirection::from($this->infoForm['direction_primary'])
                            ->axis()
                            ->perpendicular()
                            ->directions()
                    ),
                ],
            ]);
            // Save to database
            $this->neography->fill($this->infoForm);
            $this->neography->save();
            // Refresh front-end state
            $this->refreshInfoForm();
            $this->dispatch('save-info-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-info-failure');
            // Let error keep propagating
            throw $e;
        }
    }
    public function fontSave(string $afterSuccess = '', array $payload = []): void
    {
        try {
            $this->validate([
                'fontForm.css' => [
                    'nullable',
                    'string',
                    'max:2000',
                    'regex:/\A[A-Za-z0-9 \t\n\-:.;,%]*\z/',
                ],
            ]);
            $this->neography->font_css = $this->fontForm['css'];
            $this->neography->save();
            $this->dispatch('save-font-success', ['afterSuccess'=>$afterSuccess, 'payload'=>$payload]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('save-font-failure');
            // Let error keep propagating
            throw $e;
        }
    }

    /**
     * Granular CRUD-type functions
     */
    public function createSection(): void
    {
        try {
            $nextPosition = collect($this->sects)->max('position') + 1;
            $sect = CreateWithUniqueName::handle(
                startNum: $this->neography->sections()->count(),
                createFunc: fn ($tryName) => $this->neography->sections()->create([
                    'name' => $tryName,
                    'position' => $nextPosition,
                ]),
            );
        } catch (\Throwable $e) {
            $this->dispatch('sect-add-failure');
            throw $e;
        }
        $this->refreshGlyphsForm();
    }
    public function deleteSection(string $sectId): void
    {
        NeographySection::findOrFail((int)$sectId)->delete();
        $this->refreshGlyphsForm();
        app(GlobalIdGarbageCollector::class)->collect();
    }
    public function swapSections(string $sectId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($sectId, $neighborId) {
                $sectsCollection = collect($this->sects);
                $sectModel     = $sectsCollection->firstWhere('id', $sectId);
                $neighborModel = $sectsCollection->firstWhere('id', $neighborId);
                $oldSectPosition     = (int) $this->glyphsForm[$sectId]['position'];
                $oldNeighborPosition = (int) $this->glyphsForm[$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $sectsCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $sectModel->position = $oldNeighborPosition;
                $sectModel->save();
                $neighborModel->position = $oldSectPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('sect-swap-failure');
            throw $e;
        }
        $this->refreshGlyphsForm();
    }
    public function createKeyboard(): void
    {
        try {
            $nextPosition = collect($this->keyboards)->max('position') + 1;
            $keyboard = $this->neography->keyboards()->create([
                'position' => $nextPosition,
                'width' => 10,
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('keyboard-add-failure');
            throw $e;
        }
        $this->refreshKeyboardsForm();
    }
    public function updateKeyboard(string $keyboardId, string $propName, mixed $propVal, ?string $domId = ''): void
    {
        // Find model
        $keyboardModel = $this->findInCache('keyboard-update-failure', [
            [
                'id' => $keyboardId,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['keyboardId' => [__('tollerus::error.invalid_keyboard')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'width'  => ['type' => 'int', 'column' => 'width', 'min' => 1, 'max' => 40],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('keyboard-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'int':
                $num = (int)($propVal);
                if ($num < $allowedPropData[$propName]['min'] || $num > $allowedPropData[$propName]['max']) {
                    $this->dispatch('text-save-failure', id: $domId);
                    throw \Illuminate\Validation\ValidationException::withMessages(['keyboard.'.$propName => [__('tollerus::error.number_out_of_range')]]);
                }
                $keyboardModel[$allowedPropData[$propName]['column']] = $num;
            break;
        }
        // Save to database
        try {
            $keyboardModel->save();
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            $this->dispatch('keyboard-update-failure');
            throw $e;
        }
    }
    public function deleteKeyboard(string $keyboardId): void
    {
        NeographyInputKeyboard::findOrFail((int)$keyboardId)->delete();
        $this->refreshKeyboardsForm();
    }
    public function swapKeyboards(string $keyboardId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($keyboardId, $neighborId) {
                $keyboardsCollection = collect($this->keyboards);
                $keyboardModel = $keyboardsCollection->firstWhere('id', $keyboardId);
                $neighborModel = $keyboardsCollection->firstWhere('id', $neighborId);
                $oldKeyboardPosition = (int) $this->keysForm[$keyboardId]['position'];
                $oldNeighborPosition = (int) $this->keysForm[$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $keyboardsCollection->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $keyboardModel->position = $oldNeighborPosition;
                $keyboardModel->save();
                $neighborModel->position = $oldKeyboardPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('keyboard-swap-failure');
            throw $e;
        }
        $this->refreshKeyboardsForm();
    }
    public function createKey(string $keyboardId): void
    {
        // Find model
        $keyboardModel = $this->findInCache('keyboard-update-failure', [
            [
                'id' => $keyboardId,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['keyboardId' => [__('tollerus::error.invalid_keyboard')]],
            ],
        ]);
        // Create glyph
        $nextPosition = $keyboardModel->inputKeys->max('position') + 1;
        $keyboardModel->inputKeys()->create([
            'position' => $nextPosition,
        ]);
        $this->refreshKeyboardsForm();
    }
    public function updateKey(string $keyboardId, string $keyId, string $propName, string $propVal, ?string $domId = ''): void
    {
        // Find model
        $keyModel = $this->findInCache('key-update-failure', [
            [
                'id' => $keyboardId,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['keyboardId' => [__('tollerus::error.invalid_keyboard')]],
                'relation' => 'inputKeys',
            ],
            [
                'id' => $keyId,
                'objectType' => NeographyInputKey::class,
                'failMessage' => ['keyId' => [__('tollerus::error.invalid_key')]],
            ],
        ]);
        // $propName whitelist
        $allowedPropData = [
            'label'      => ['type' => 'string', 'column' => 'label'],
            'glyph'      => ['type' => 'string', 'column' => 'glyph'],
            'glyphHex'   => ['type' => 'hex', 'column' => 'glyph'],
            'renderBase' => ['type' => 'boolean', 'column' => 'render_base'],
        ];
        $allowedPropNames = array_keys($allowedPropData);
        if (!in_array($propName, $allowedPropNames, true)) {
            $this->dispatch('key-update-failure');
            throw \Illuminate\Validation\ValidationException::withMessages([$propName => [__('tollerus::error.invalid_prop_name')]]);
        }
        // Assign appropriately by type
        switch ($allowedPropData[$propName]['type']) {
            case 'boolean':
                $keyModel[$allowedPropData[$propName]['column']] = (bool) filter_var($propVal, FILTER_VALIDATE_BOOLEAN);
            break;
            case 'hex':
                $valClean = str_replace(' ', '', $propVal);
                $valChars = explode(',', $valClean);
                $glyphChars = '';
                foreach ($valChars as $char) {
                    $glyphChars .= mb_chr(hexdec($char));
                }
                $keyModel[$allowedPropData[$propName]['column']] = $glyphChars;
            break;
            case 'string':
            default:
                $keyModel[$allowedPropData[$propName]['column']] = $propVal;
            break;
        }
        // Save to database
        try {
            $keyModel->save();
            $this->refreshKeyboardsForm(); // This is needed because 'glyph' and 'glyphHex' both access the same DB column
            $this->dispatch('text-save-success', id: $domId);
        } catch (\Throwable $e) {
            if ($e instanceof \Illuminate\Database\UniqueConstraintViolationException) {
                $this->dispatch('text-save-failure', id: $domId);
                throw \Illuminate\Validation\ValidationException::withMessages(['key.'.$propName => [__('tollerus::error.duplicate_of_key')]]);
            } else {
                $this->dispatch('key-update-failure');
                throw $e;
            }
        }
    }
    public function deleteKey(string $keyId): void
    {
        NeographyInputKey::findOrFail((int)$keyId)->delete();
        $this->refreshKeyboardsForm();
    }
    public function swapKeys(string $keyboardId, string $keyId, string $neighborId): void
    {
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($keyboardId, $keyId, $neighborId) {
                $keyboardModel = collect($this->keyboards)->firstWhere('id', $keyboardId);
                $keyModel      = $keyboardModel->inputKeys->firstWhere('id', $keyId);
                $neighborModel = $keyboardModel->inputKeys->firstWhere('id', $neighborId);
                $oldKeyPosition      = (int) $this->keysForm[$keyboardId]['keys'][$keyId]['position'];
                $oldNeighborPosition = (int) $this->keysForm[$keyboardId]['keys'][$neighborId]['position'];
                /**
                 * Apparently the 'unique' constraint applies even within a transaction.
                 * So we need to carefully move one of the models out of the way first.
                 */
                $minPosition = $keyboardModel->inputKeys->min('position');
                $neighborModel->position = $minPosition - 1;
                $neighborModel->save();
                /**
                 * And finally we can just set and save both correct values.
                 */
                $keyModel->position = $oldNeighborPosition;
                $keyModel->save();
                $neighborModel->position = $oldKeyPosition;
                $neighborModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('key-swap-failure');
            throw $e;
        }
        $this->refreshKeyboardsForm();
    }
    public function transferKey(string $keyboardId, string $keyId, string $destKeyboard): void
    {
        // Basic sanity check
        if ($keyboardId == $destKeyboard) {
            $this->dispatch('key-transfer-failure');
            throw \Illuminate\Validation\ValidationException::withMessages(['destKeyboard' => [__('tollerus::error.invalid_keyboard')]]);
        }
        // Find models
        $keyboardModel = $this->findInCache('key-transfer-failure', [
            [
                'id' => $keyboardId,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['keyboardId' => [__('tollerus::error.invalid_keyboard')]],
            ],
        ]);
        $destKeyboardModel = $this->findInCache('key-transfer-failure', [
            [
                'id' => $destKeyboard,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['destKeyboard' => [__('tollerus::error.invalid_keyboard')]],
            ],
        ]);
        $keyModel = $this->findInCache('key-transfer-failure', [
            [
                'id' => $keyboardId,
                'objectType' => NeographyInputKeyboard::class,
                'failMessage' => ['keyboardId' => [__('tollerus::error.invalid_keyboard')]],
                'relation' => 'inputKeys',
            ],
            [
                'id' => $keyId,
                'objectType' => NeographyInputKey::class,
                'failMessage' => ['keyId' => [__('tollerus::error.invalid_key')]],
            ],
        ]);
        // Transfer key
        try {
            $connection = config('tollerus.connection', 'tollerus');
            DB::connection($connection)->transaction(function () use ($keyboardId, $keyboardModel, $keyId, $keyModel, $destKeyboard, $destKeyboardModel) {
                /**
                 * To safely transplant the key, we have to make sure that during
                 * the transition its 'position' property doesn't conflict with any
                 * in EITHER keyboard.
                 */
                $minPosition = min(
                    $keyboardModel->inputKeys->min('position'),
                    $destKeyboardModel->inputKeys->min('position')
                );
                $nextPosition = $destKeyboardModel->inputKeys->max('position') + 1;
                // Move to universally safe position
                $keyModel->position = $minPosition - 1;
                $keyModel->save();
                // Transplant to destination keyboard
                $keyModel->keyboard_id = $destKeyboardModel->id;
                $keyModel->save();
                // Move to final position in new keyboard
                $keyModel->position = $nextPosition;
                $keyModel->save();
            });
        } catch (\Throwable $e) {
            $this->dispatch('key-transfer-failure');
            throw $e;
        }
        $this->refreshKeyboardsForm();
    }

    /**
     * Public utility functions
     */
    public function publishFont(FontFormat $fontFormat): void
    {
        $fontAssetService = new FontAssetService;
        try {
            $fontAssetService->publish($fontFormat, $this->neography);
        } catch (\Throwable $e) {
            $this->dispatch('publish-font-failure');
            throw $e;
        }
        $this->refreshFontForm();
    }
    public function deleteFont(FontFormat $fontFormat): void
    {
        $fontAssetService = new FontAssetService;
        try {
            if (!empty($this->neography->{$fontFormat->pathColumn()})) {
                $fontAssetService->delete($fontFormat, $this->neography);
            }
            $this->neography->{$fontFormat->blobColumn()} = null;
            $this->neography->save();
        } catch (\Throwable $e) {
            $this->dispatch('font-delete-failure');
            throw $e;
        }
        $this->refreshFontForm();
    }
    public function extractSvgToGlyphs(): void
    {
        $extractAction = new SvgToGlyphs;
        try {
            $extractAction($this->neography);
            $this->refreshGlyphsForm();
            $this->dispatch('svgtoglyphs-success');
        } catch (\Throwable $e) {
            $this->dispatch('svgtoglyphs-failure');
            return;
        }
    }
    public function extractSvgToKeyboard(): void
    {
        $extractAction = new SvgToKeyboard;
        try {
            $extractAction($this->neography);
            $this->refreshKeyboardsForm();
            $this->dispatch('svgtokeys-success');
        } catch (\Throwable $e) {
            $this->dispatch('svgtokeys-failure');
            return;
        }
    }
    public function importGlyphsToKeyboard(): void
    {
        $importAction = new GlyphsToKeyboard;
        try {
            $importAction($this->neography);
            $this->refreshKeyboardsForm();
            $this->dispatch('glyphstokeys-success');
        } catch (\Throwable $e) {
            $this->dispatch('glyphstokeys-failure');
            return;
        }
    }
}
