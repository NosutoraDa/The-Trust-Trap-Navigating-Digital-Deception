<?php

namespace App\Http\Controllers;

use App\Services\MetaDefenderFileChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class FileScanController extends Controller
{
    public function store(Request $request, MetaDefenderFileChecker $checker): JsonResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:10240'],
            'share_file_with_opswat' => ['required', 'accepted'],
        ]);

        $key = 'guardian:files:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'error' => 'Batas pemindaian file aplikasi tercapai. Coba lagi dalam satu menit.',
            ], 429);
        }

        RateLimiter::hit($key, 60);
        $result = $checker->scan($data['file']);

        if (isset($result['scan_id'])) {
            $request->session()->put('guardian_file_scan_id', $result['scan_id']);
            unset($result['scan_id']);
        } else {
            $request->session()->forget('guardian_file_scan_id');
        }

        return response()->json($result);
    }

    public function analysis(Request $request, MetaDefenderFileChecker $checker): JsonResponse
    {
        $dataId = $request->session()->get('guardian_file_scan_id');

        if (! is_string($dataId) || preg_match('/\A[A-Za-z0-9_-]+\z/', $dataId) !== 1) {
            return response()->json([
                'error' => 'Tidak ada pemindaian file tertunda untuk sesi ini.',
            ], 404);
        }

        $key = 'guardian:file-poll:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 6)) {
            return response()->json([
                'error' => 'Terlalu banyak permintaan status. Tunggu sebelum mencoba lagi.',
            ], 429);
        }

        RateLimiter::hit($key, 60);
        $result = $checker->checkAnalysis($dataId);

        if ($result['status'] !== 'pending') {
            $request->session()->forget('guardian_file_scan_id');
        }

        return response()->json($result);
    }
}
