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
        // $languages->loadMissing([
        //     'neographies',
        // ]);
        return view('tollerus::admin.languages.index', [
            'languages' => $languages,
        ]);
    }
}
