<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Actions\CreateWithUniqueName;
use PeterMarkley\Tollerus\Maintenance\GlobalIdGarbageCollector;
use PeterMarkley\Tollerus\Models\Neography;

class NeographyController extends Controller
{
    /**
     * List all neographies
     */
    public function index()
    {
        $neographies = Neography::orderBy('machine_name')
            ->get();
        $glyphPreview = $neographies->mapWithKeys(function ($n) {
            $glyphs = $n->glyphs()
                ->orderBy('group_id')
                ->orderBy('position')
                ->where('render_base', false)
                ->limit(50)->get()
                ->map(fn ($g) => [
                    'model' => $g,
                    'svg' => $g->getSvg('h-12 w-auto'),
                ]);
            return [$n->machine_name => $glyphs];
        })->all();
        $deleteMsgs = $neographies
            ->mapWithKeys(function ($n) {
                $count = $n->nativeSpellings()->count();
                return [$n->machine_name => __('tollerus::ui.delete_neography_confirmation', [
                    'name' => $n->name,
                    'num' => number_format($count),
                ])];
            })->all();
        return view('tollerus::admin.neographies.index', [
            'breadcrumbs' => [
                ['href' => route('tollerus.admin.index'), 'text' => __('tollerus::ui.admin')],
            ],
            'neographies' => $neographies,
            'glyphPreview' => $glyphPreview,
            'deleteMsgs' => $deleteMsgs,
        ]);
    }

    /**
     * Create new neography
     */
    public function store()
    {
        $neography = CreateWithUniqueName::handle(
            startNum: Neography::count(),
            createFunc: fn ($tryName) => Neography::create([
                'name' => $tryName,
                'machine_name' => strtr(mb_strtolower($tryName), [
                    ' ' => '_',
                    '(' => '',
                    ')' => '',
                ]),
            ]),
        );
        return response()->json(['id' => $neography->id]);
    }

    /**
     * Delete existing neography
     */
    public function destroy(Neography $neography)
    {
        $neography->delete();
        app(GlobalIdGarbageCollector::class)->collect();
        return response()->noContent();
    }
}
