<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
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
            'wordClassGroups.primaryClass',
            'entries.primaryForm'
        ]);
        // Preview of neography data
        $primaryGlyphs = $languages->mapWithKeys(function ($l) {
            if ($l->primaryNeography !== null) {
                $glyphs = $l->primaryNeography->glyphs()
                    ->where('render_base', false)
                    ->limit(2)
                    ->get();
                $svg = $glyphs->map(fn($g)=>$g->getSvg('h-12 w-auto pointer-events-none'));
                $output = [
                    'models' => $glyphs,
                    'svg' => $svg,
                    'allSvgFound' => !($svg->contains(fn($g)=>$g===null)),
                ];
            } else {
                $output = null;
            }
            return [$l->machine_name => $output];
        })->all();
        // Preview of grammar data
        $wordClassGroups = $languages->mapWithKeys(function ($l) {
            $groups = $l->wordClassGroups->map(function ($item) {
                $class = $item->primaryClass ?? $item->wordClasses()->first();
                if ($class !== null) {
                    return [
                        'class' => $class,
                        'nameBrief' => $class->name_brief ?? mb_substr($class->name,0,3),
                        'featureCount' => $item->features()->count(),
                    ];
                } else {
                    return [
                        'class' => null,
                        'nameBrief' => null,
                        'featureCount' => 0,
                    ];
                }
            });
            return [$l->machine_name => $groups];
        })->all();
        // Preview of entry data
        $entriesPreview = $languages->mapWithKeys(function ($l) {
            $entries = $l->forms()
                ->whereExists(function ($query) {
                    $query->select(\DB::raw(1))
                    ->from('entries')
                    ->whereColumn('entries.primary_form', 'forms.id');
                })
                ->orderBy('transliterated')
                ->limit(50)
                ->get();
            return [$l->machine_name => $entries];
        })->all();
        $deleteMsgs = $languages
            ->mapWithKeys(function ($l) {
                $count = $l->entries->count();
                return [$l->machine_name => __('tollerus::ui.delete_language_confirmation', [
                    'name' => $l->name,
                    'num' => number_format($count),
                ])];
            })->all();
        // Pass data to view
        return view('tollerus::admin.languages.index', [
            'breadcrumbs' => [
                ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
            ],
            'languages' => $languages,
            'primaryGlyphs' => $primaryGlyphs,
            'wordClassGroups' => $wordClassGroups,
            'entriesPreview' => $entriesPreview,
            'deleteMsgs' => $deleteMsgs,
        ]);
    }

    /**
     * Create new language
     */
    public function store()
    {
        $language = CreateWithUniqueName::handle(
            startNum: Language::count(),
            createFunc: fn ($tryName) => Language::create([
                'name' => $tryName,
                'machine_name' => strtr(mb_strtolower($tryName), [
                    ' ' => '_',
                    '(' => '',
                    ')' => '',
                ]),
            ]),
        );
        return response()->json(['id' => $language->id]);
    }

    /**
     * Delete existing language
     */
    public function destroy(Language $language)
    {
        $language->delete();
        app(GlobalIdGarbageCollector::class)->collect();
        return response()->noContent();
    }
}
