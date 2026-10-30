<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaDefenderFileChecker
{
    private const API_URL = 'https://api.metadefender.com/v4';

    public function scan(UploadedFile $file): array
    {
        $apiKey = config('guardian.metadefender.api_key');

        if (! \is_string($apiKey) || $apiKey === '') {
            return $this->result('unavailable', 'OPSWAT MetaDefender belum dikonfigurasi. Tambahkan OPSWAT_API_KEY ke environment server.');
        }

        $stream = fopen($file->getRealPath(), 'rb');

        if ($stream === false) {
            Log::warning('MetaDefender could not read the temporary uploaded file.');

            return $this->result('unavailable', 'File tidak dapat dibaca untuk diperiksa.');
        }

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file->getClientOriginalName())) ?: 'uploaded-file';

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Content-Type' => 'application/octet-stream',
                'samplesharing' => '0',
                'filename' => substr($filename, 0, 120),
            ])
                ->connectTimeout(5)
                ->timeout(45)
                ->send('POST', self::API_URL.'/file', ['body' => $stream]);
        } catch (ConnectionException $exception) {
            return $this->connectionFailure($exception);
        } finally {
            fclose($stream);
        }

        if (! $response->successful()) {
            return $this->upstreamFailure($response->status());
        }

        $dataId = data_get($response->json(), 'data_id');

        if (! \is_string($dataId) || preg_match('/\A[A-Za-z0-9_-]+\z/', $dataId) !== 1) {
            Log::warning('MetaDefender upload did not return a valid scan ID.');

            return $this->result('unavailable', 'Layanan pemindai tidak mengembalikan ID pemeriksaan yang valid.');
        }

        $result = $this->report(data_get($response->json(), 'scan_results'));

        if ($result['status'] === 'pending') {
            $result['scan_id'] = $dataId;
        }

        return $result;
    }

    public function checkAnalysis(string $dataId): array
    {
        $apiKey = config('guardian.metadefender.api_key');

        if (! \is_string($apiKey) || $apiKey === '') {
            return $this->result('unavailable', 'OPSWAT MetaDefender belum dikonfigurasi. Tambahkan OPSWAT_API_KEY ke environment server.');
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'x-file-metadata' => '1',
            ])
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->get(self::API_URL.'/file/'.rawurlencode($dataId));
        } catch (ConnectionException $exception) {
            return $this->connectionFailure($exception);
        }

        if (! $response->successful()) {
            return $this->upstreamFailure($response->status());
        }

        return $this->report(data_get($response->json(), 'scan_results'));
    }

    private function report(mixed $scanResults): array
    {
        if (! \is_array($scanResults) || ! is_numeric($scanResults['progress_percentage'] ?? null)) {
            Log::warning('MetaDefender scan report did not include progress information.');

            return $this->result('unavailable', 'Layanan pemindai memberikan laporan yang tidak valid.');
        }

        if ((float) $scanResults['progress_percentage'] < 100) {
            return [
                'status' => 'pending',
                'verdict' => null,
                'message' => 'Pemindaian file masih berlangsung. Perbarui status sebentar lagi.',
            ];
        }

        $detections = is_numeric($scanResults['total_detected_avs'] ?? null)
            ? (int) $scanResults['total_detected_avs']
            : 0;
        $engines = is_numeric($scanResults['total_avs'] ?? null)
            ? (int) $scanResults['total_avs']
            : 0;

        return [
            'status' => 'complete',
            'verdict' => $detections > 0 ? 'detected' : 'no_detections',
            'message' => $detections > 0
                ? 'Satu atau lebih mesin pemindai menandai file ini. Jangan buka file tersebut.'
                : 'Tidak ada deteksi dari pemindai saat ini. Ini bukan jaminan bahwa file aman.',
            'detections' => $detections,
            'engines' => $engines,
        ];
    }

    private function connectionFailure(ConnectionException $exception): array
    {
        Log::warning('MetaDefender file request could not connect.', [
            'message' => $exception->getMessage(),
        ]);

        return $this->result('unavailable', 'OPSWAT MetaDefender tidak dapat dihubungi. Coba lagi nanti.');
    }

    private function upstreamFailure(int $status): array
    {
        if ($status === 429) {
            return $this->result('rate_limited', 'Batas permintaan OPSWAT tercapai. Tunggu sebelum memindai file lain.');
        }

        Log::warning('MetaDefender file request failed.', ['status' => $status]);

        return $this->result(
            'unavailable',
            $status === 401 || $status === 403
                ? 'OPSWAT menolak API key atau akun ini tidak memiliki izin.'
                : 'OPSWAT MetaDefender tidak dapat memberikan hasil. Coba lagi nanti.'
        );
    }

    private function result(string $status, string $message): array
    {
        return [
            'status' => $status,
            'verdict' => null,
            'message' => $message,
        ];
    }
}
