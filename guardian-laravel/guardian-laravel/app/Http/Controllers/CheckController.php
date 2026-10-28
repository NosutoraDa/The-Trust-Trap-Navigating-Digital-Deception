<?php

namespace App\Http\Controllers;

use App\Services\ScamChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function store(Request $request, ScamChecker $checker): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
        ]);

        $result = $checker->analyze($data['text']);

        // Keep the draft on the server so the Bertindak page can show it.
        $request->session()->put('guardian_draft', $result['draft']);

        return response()->json($result);
    }
}
