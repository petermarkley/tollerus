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
        // $neographies->loadMissing([
        //     'languages',
        // ]);
        return view('tollerus::admin.neographies.index', [
            'neographies' => $neographies,
        ]);
    }
}
