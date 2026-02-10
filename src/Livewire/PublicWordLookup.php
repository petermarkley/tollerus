<?php

namespace PeterMarkley\Tollerus\Livewire;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

use PeterMarkley\Tollerus\Enums\GlobalIdKind;
use PeterMarkley\Tollerus\Enums\SearchType;
use PeterMarkley\Tollerus\Models\GlobalId;
use PeterMarkley\Tollerus\Models\Language;

class PublicWordLookup extends Component
{
    #[Locked] public Collection $languages;
    public ?string $id;

    /**
     * Livewire hooks
     */
    public function render(Request $req): View
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' My test page';
        }

        return view('tollerus::livewire.public-word-lookup', [
                // 'id' => $this->id,
            ])->layout('tollerus::components.layouts.public')
            ->title($pageTitle);
    }
    public function mount(Request $req): void
    {
        $this->languages = Language::where('visible', true)->get();

        /**
         * We need to check if the user has specified a global ID,
         * and if so we need to validate & resolve it.
         *
         * The only canonical variants of this page we should
         * accept are entry IDs. Other IDs underneath that should
         * redirect to the entry ID with the appropriate document
         * fragment inside it.
         *
         * The only other thing we want to catch is a glyph ID,
         * which we should redirect to an appropriate language
         * detail page (again, with document fragment).
         */
        $this->id = $req->query('id', null);
        if ($this->id !== null) {
            // Look up global ID and its associated model
            $globalId = GlobalId::fromStr($this->id);
            if (!($globalId instanceof GlobalId)) {
                abort(404);
            }
            $obj = $globalId->resolve();
            // What kind of model is this?
            switch ($globalId->kind) {
                case GlobalIdKind::Glyph:
                    /**
                     * This is a glyph inside a neography. Let's try to
                     * redirect to an appropriate language page.
                     */
                    $neography = $obj->neography;
                    $language = $neography->languagesWherePrimary->firstWhere('visible', true) ?? $neography->languages->firstWhere('visible', true);
                    if (!($language instanceof Language)) {
                        abort(404);
                    }
                    $this->redirect(route('tollerus.public.languages.show', ['language' => $language]) . '#'.$this->id);
                break;
                case GlobalIdKind::Entry:
                    // Nothing to do
                break;
                case GlobalIdKind::Lexeme:
                    /**
                     * For a lexeme, we need to redirect to the entry
                     */
                    $entry = $obj->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id]) . '#'.$this->id);
                break;
                case GlobalIdKind::Form:
                    /**
                     * For a form, we also need to redirect to the entry
                     */
                    $entry = $obj->lexeme->entry;
                    $this->redirect(route('tollerus.public.index', ['id' => $entry->global_id]) . '#'.$this->id);
                break;
            }
        }
    }

    public function search()
    {
        $this->resetPage();
    }
}
