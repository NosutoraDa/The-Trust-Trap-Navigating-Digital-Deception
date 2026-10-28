<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $question = mb_strtolower($data['question']);

        // Demo: canned answers from config/guardian.php.
        // Replace this with a call to the Claude API when it is ready.
        foreach (config('guardian.qa') as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (str_contains($question, $keyword)) {
                    return response()->json(['answer' => $item['answer']]);
                }
            }
        }

        return response()->json(['answer' => config('guardian.qa_fallback')]);
    }
}
