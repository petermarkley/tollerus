<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

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
        return view('tollerus::admin.neographies.index', [
            'neographies' => $neographies,
            'glyphPreview' => $glyphPreview,
        ]);
    }
}
