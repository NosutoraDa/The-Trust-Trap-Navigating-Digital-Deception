<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AskController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $token = config('guardian.ai.token');

        if (! \is_string($token) || $token === '') {
            return response()->json([
                'error' => 'Chatbot belum dikonfigurasi. Tambahkan HUGGINGFACE_API_TOKEN di environment server.',
            ], 503);
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(30)
                ->post(config('guardian.ai.endpoint'), [
                    'model' => config('guardian.ai.model'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah Guardian, asisten edukasi keamanan digital berbahasa Indonesia. Jawab dengan jelas dan ringkas. Jangan pernah meminta OTP, PIN, kata sandi, atau data pribadi. Ingatkan pengguna untuk memverifikasi informasi melalui kanal resmi.',
                        ],
                        ['role' => 'user', 'content' => $data['question']],
                    ],
                    'max_tokens' => 400,
                    'temperature' => 0.4,
                ]);
        } catch (ConnectionException $exception) {
            Log::warning('Hugging Face chat request could not connect.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Layanan chatbot sedang tidak dapat dihubungi. Coba lagi sebentar lagi.',
            ], 502);
        }

        if (! $response->successful()) {
            Log::warning('Hugging Face chat request failed.', [
                'status' => $response->status(),
            ]);

            return response()->json([
                'error' => 'Layanan chatbot gagal menjawab. Coba lagi sebentar lagi.',
            ], 502);
        }

        $answer = data_get($response->json(), 'choices.0.message.content');

        if (! \is_string($answer) || trim($answer) === '') {
            Log::warning('Hugging Face chat response did not contain an answer.');

            return response()->json([
                'error' => 'Layanan chatbot memberikan jawaban yang tidak valid. Coba lagi.',
            ], 502);
        }

        return response()->json(['answer' => trim($answer)]);
    }
}
