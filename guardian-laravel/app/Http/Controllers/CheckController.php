<?php

namespace App\Http\Controllers;

use App\Services\VirusTotalUrlChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckController extends Controller
{
    public function store(
        Request $request,
        VirusTotalUrlChecker $virusTotal
    ): JsonResponse {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:2048'],
            'share_url_with_virustotal' => ['required', 'accepted'],
        ]);

        return response()->json([
            'virustotal' => $virusTotal->checkText($data['text']),
        ]);
    }

    public function analysis(Request $request, VirusTotalUrlChecker $virusTotal): JsonResponse
    {
        $data = $request->validate([
            'analysis_id' => ['required', 'string', 'max:200', 'regex:/\A[A-Za-z0-9_-]+\z/'],
            'url_id' => ['required', 'string', 'max:200', 'regex:/\A[A-Za-z0-9_-]+\z/'],
        ]);

        return response()->json($virusTotal->checkAnalysis($data['analysis_id'], $data['url_id']));
    }
}
