<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Language;

class PublicLanguageController extends Controller
{
    /**
     * List all languages
     */
    public function index()
    {
        $languages = Language::orderBy('machine_name')
            ->where('visible', true)
            ->get();

        if ($languages->count() == 0) {
            return redirect()->route('tollerus.public.index');
        } else if ($languages->count() == 1) {
            return redirect()->route('tollerus.public.languages.show', ['language' => $languages->first()]);
        }

        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' ' . trans_choice('tollerus::ui.language_info', $languages->count());
        }

        return view('tollerus::public.languages.index', [
            'languages' => $languages,
            'title' => $pageTitle,
        ]);
    }

    /**
     * Show single language
     */
    public function show(Language $language)
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' ' . $language->name;
        }
        $langCount = Language::where('visible', true)->count();
        return view('tollerus::public.languages.show', [
            'breadcrumbs' => [
                ['href' => route('tollerus.public.languages.index'), 'text' => trans_choice('tollerus::ui.language_info', $langCount)],
            ],
            'language' => $language,
            'neographies' => $language->neographies->where('visible', true)->sortBy('machine_name'),
            'langCount' => $langCount,
            'title' => $pageTitle,
        ]);
    }
}
