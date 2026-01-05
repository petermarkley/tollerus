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
        $hasData = (bool)((Language::count() + Neography::count()) > 0);
        return view('tollerus::admin.index', ['hasData' => $hasData]);
    }
}
