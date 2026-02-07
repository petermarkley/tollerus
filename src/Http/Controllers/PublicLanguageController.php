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
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' ' . trans_choice('tollerus::ui.language_info', $languages->count());
        }

        return view('tollerus::public.languages.index', [
            'breadcrumbs' => [
                ['href' => route('tollerus.public.index'), 'text' => __('tollerus::ui.word_lookup')],
            ],
            'languages' => $languages,
            'title' => $pageTitle,
        ]);
    }
}
