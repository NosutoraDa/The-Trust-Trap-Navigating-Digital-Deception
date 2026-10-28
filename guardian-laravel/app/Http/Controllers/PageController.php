<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function cek(): View
    {
        return view('pages.cek', [
            'examples' => config('guardian.examples'),
        ]);
    }

    public function belajar(): View
    {
        return view('pages.belajar', [
            'scenarios' => config('guardian.scenarios'),
        ]);
    }

    public function bertindak(Request $request): View
    {
        return view('pages.bertindak', [
            'draft' => $request->session()->get(
                'guardian_draft',
                'Belum ada draf. Periksa pesan di tab Cek terlebih dahulu.'
            ),
        ]);
    }

    public function tanya(): View
    {
        return view('pages.tanya');
    }
}
