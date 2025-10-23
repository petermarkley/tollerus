<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class LanguageController extends Controller
{
    /**
     * List all languages
     */
    public function index()
    {
        return view('tollerus::admin.languages.index');
    }
}
