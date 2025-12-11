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
    // public array $infoForm = [];
    // UI display properties
    // #[Locked] public array $nativeSpellingCounts = [];

    /**
     * Livewire hooks
     */
    public function render(): View
    {
        return view('tollerus::livewire.neography-editor')
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
        // $this->refreshInfoForm();

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
                // $this->refreshInfoForm();
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

    /**
     * Tab-specific refresh functions
     */
    // public function refreshInfoForm(): void
    // {
    //     //
    // }
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
}
