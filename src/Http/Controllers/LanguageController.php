<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Language;

class LanguageController extends Controller
{
    /**
     * List all languages
     */
    public function index()
    {
        $languages = Language::orderBy('machine_name')
            ->get();
        $languages->loadMissing([
            'primaryNeography',
        ]);
        $primaryGlyphs = $languages->mapWithKeys(function ($l) {
            if ($l->primaryNeography !== null) {
                $glyphs = $l->primaryNeography->glyphs()
                    ->where('render_base', false)
                    ->limit(2)
                    ->get();
            } else {
                $glyphs = null;
            }
            return [$l->machine_name => $glyphs];
        })->all();
        return view('tollerus::admin.languages.index', [
            'languages' => $languages,
            'primaryGlyphs' => $primaryGlyphs,
        ]);
    }
}
