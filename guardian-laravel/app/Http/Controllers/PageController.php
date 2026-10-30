<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function links(): View
    {
        return view('pages.links', [
            'examples' => config('guardian.examples'),
        ]);
    }

    public function files(): View
    {
        return view('pages.files');
    }

    public function tanya(): View
    {
        return view('pages.tanya');
    }
}
