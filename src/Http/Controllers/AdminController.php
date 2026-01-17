<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Neography;
use PeterMarkley\Tollerus\Models\Language;

class AdminController extends Controller
{
    public function index(): View
    {
        $languageCount = Language::count();
        $neographyCount = Neography::count();
        $hasData = (bool)(($languageCount + $neographyCount) > 0);
        return view('tollerus::admin.index', [
            'languageCount' => $languageCount,
            'neographyCount' => $neographyCount,
            'hasData' => $hasData,
        ]);
    }
}
