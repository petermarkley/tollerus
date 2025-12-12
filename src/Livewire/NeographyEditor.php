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
use PeterMarkley\Tollerus\Enums\WritingDirection;
use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Traits\HasModelCache;

class NeographyEditor extends Component
{
    // use HasModelCache;
    // private $cacheRoot = '';
    public string $tab = 'info';
    // Models
    #[Locked] public Neography $neography;
    // UI input layer
    public array $infoForm = [];
    // UI display properties
    #[Locked] public array $writingDirectionOpts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.neography-editor', ['writingDirectionOpts' => $this->writingDirectionOpts])
            ->layout('tollerus::components.layout', [
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
            })
            ->toArray();

        // Font tab
        // $this->refreshFontForm();

        // Glyphs tab
        // $this->refreshGlyphsForm();

        // Keyboards tab
        // $this->refreshKeyboardsForm();
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
                // $this->refreshFontForm();
            break;
            case 'glyphs':
                // $this->refreshGlyphsForm();
            break;
            case 'keyboards':
                // $this->refreshKeyboardsForm();
            break;
        }
    }
    public function save(string $tab, string $afterSuccess = '', array $payload = []): void
    {
        switch ($tab) {
            case 'info':
                $this->infoSave($afterSuccess, $payload);
            break;
            // case 'font':
            //     $this->fontSave($afterSuccess, $payload);
            // break;
            // case 'glyphs':
            //     $this->glyphsSave($afterSuccess, $payload);
            // break;
            // case 'keyboards':
            //     $this->keyboardsSave($afterSuccess, $payload);
            // break;
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
    // public function refreshFontForm(): void
    // {
    //     //
    // }
    // public function refreshGlyphsForm(): void
    // {
    //     //
    // }
    // public function refreshKeyboardsForm(): void
    // {
    //     //
    // }

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
}
