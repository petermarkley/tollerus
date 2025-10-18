<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class HelloController extends Controller
{
    public function index(): View
    {
        return view('tollerus::hello');
    }
}
