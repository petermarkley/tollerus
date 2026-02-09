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
        $neographies = $language->neographies->where('visible', true)->sortBy('machine_name');
        if ($language->primary_neography !== null && $language->primaryNeography->visible) {
            $startingNeography = $language->primary_neography;
        } else {
            $startingNeography = $neographies->first()?->id;
        }
        return view('tollerus::public.languages.show', [
            'breadcrumbs' => [
                ['href' => route('tollerus.public.languages.index'), 'text' => trans_choice('tollerus::ui.language_info', $langCount)],
            ],
            'title' => $pageTitle,
            'language' => $language,
            'neographies' => $neographies,
            'langCount' => $langCount,
            'startingNeography' => $startingNeography,
        ]);
    }

    /**
     * List all entries of a single language
     */
    public function entries(Language $language, Request $req)
    {
        $pageTitle = config('tollerus.public_page_title_base', 'Tollerus');
        if (config('tollerus.public_page_title_append', true)) {
            $pageTitle .= ' ' . __('tollerus::ui.all_entries_for_language', ['lang' => $language->name]);
        }
        $langCount = Language::where('visible', true)->count();
        $neographyId = $language->primaryNeography?->id;

        $sortBy = $req->query('sort', 'transliterated');

        /**
         * It's best if we do all of our data prep and sorting
         * in the database here, because we're paginating the
         * result.
         */
        $hasEntries = $language->entries()->exists();
        $entriesQuery = $language->entries()
            ->leftJoin('forms as pf', 'pf.id', '=', 'entries.primary_form')
            ->leftJoin('native_spellings as ns', function ($join) use ($neographyId) {
                $join->on('ns.form_id', '=', 'pf.id');
                $join->where('ns.neography_id', '=', $neographyId ?? -1);
            })->select([
                'entries.*',
                'pf.transliterated as transliterated',
                'ns.spelling as native',
                'ns.sort_key as sort_key',
            ]);
        // Set the sort method
        switch ($sortBy) {
            case 'transliterated':
                $entriesQuery->orderBy('pf.transliterated');
            break;
            case 'native':
                $entriesQuery->orderBy('ns.sort_key');
            break;
        }
        // Run the query
        $paginator = $entriesQuery->paginate(48);

        return view('tollerus::public.languages.entries', [
            'paginator' => $paginator,
            'hasEntries' => $hasEntries,
            'sortBy' => $sortBy,
            'breadcrumbs' => [
                ['href' => route('tollerus.public.languages.index'), 'text' => trans_choice('tollerus::ui.language_info', $langCount)],
                ['href' => route('tollerus.public.languages.show', ['language' => $language]), 'text' => $language->name],
            ],
            'title' => $pageTitle,
            'language' => $language,
            'langCount' => $langCount,
        ]);
    }
}
