<?php

namespace PeterMarkley\Tollerus\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

use PeterMarkley\Tollerus\Models\Language;
use PeterMarkley\Tollerus\Models\Entry;

class EntryController extends Controller
{
    /**
     * Create new entry
     */
    public function store(Language $language)
    {
        $entry = $language->entries()->create();
        return response()->json(['id' => $entry->id]);
    }

    /**
     * Delete existing entry
     */
    public function destroy(Language $language, Entry $entry)
    {
        $entry->delete();
        return response()->noContent();
    }
}
