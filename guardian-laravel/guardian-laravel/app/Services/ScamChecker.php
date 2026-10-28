<?php

namespace App\Services;

class ScamChecker
{
    /**
     * Rule-based checks. 'w' is the weight. 'pattern' must match,
     * and 'also' (optional) must match too.
     */
    private const RULES = [
        ['w' => 4, 'label' => 'Meminta instal file .APK di luar Play Store (modus pembobolan OTP).', 'pattern' => '/\.apk\b/i'],
        ['w' => 2, 'label' => 'Tautan memakai layanan gratisan atau domain yang sering dipakai penipu.', 'pattern' => '/(blogspot|weebly|wixsite|\.tk\b|\.ml\b|\.ga\b|\.cf\b|bit\.ly|tinyurl)/i'],
        ['w' => 2, 'label' => 'Nama bank atau layanan resmi dipakai di tautan yang bukan domain resminya.', 'pattern' => '/(mandiri|bca|bri|bni|dana|gopay|shopee|ovo|jne)/i', 'also' => '/https?:\/\//i'],
        ['w' => 3, 'label' => 'Meminta kata sandi, PIN, atau OTP. Lembaga resmi tidak pernah memintanya lewat chat.', 'pattern' => '/(otp|pin\b|kata sandi|password|kode verifikasi)/i'],
        ['w' => 2, 'label' => 'Tekanan waktu agar kamu bertindak tanpa berpikir.', 'pattern' => '/(segera|hari ini|dalam \d+ (jam|menit)|terakhir|akan diblokir|ditangguhkan|batas waktu)/i'],
        ['w' => 3, 'label' => 'Meminta transfer, biaya admin, atau deposit di awal.', 'pattern' => '/(transfer|biaya admin|deposit|bayar|top ?up|pulsa)/i'],
        ['w' => 2, 'label' => 'Pembayaran QRIS atau rekening yang mengarah ke nama perorangan.', 'pattern' => '/(qris|rekening)/i', 'also' => '/(atas nama|a\.n\.|pribadi|perorangan)/i'],
        ['w' => 2, 'label' => 'Iming-iming hadiah, undian, atau giveaway yang tidak pernah kamu ikuti.', 'pattern' => '/(menang|giveaway|undian|hadiah|gratis)/i'],
        ['w' => 2, 'label' => 'Tawaran kerja dengan komisi besar, sering disertai syarat bayar di awal.', 'pattern' => '/(lowongan|paruh waktu|komisi|kerja dari rumah)/i'],
        ['w' => 1, 'label' => 'Mengaku dari instansi atau kurir. Verifikasi identitasnya lewat kanal resmi.', 'pattern' => '/(kurir|paket|pajak|polisi|kejaksaan|pihak bank)/i'],
    ];

    public function analyze(string $text): array
    {
        $hits = [];
        $score = 0;

        foreach (self::RULES as $rule) {
            $matched = preg_match($rule['pattern'], $text)
                && (! isset($rule['also']) || preg_match($rule['also'], $text));

            if ($matched) {
                $hits[] = $rule['label'];
                $score += $rule['w'];
            }
        }

        if ($score >= 5) {
            $title = 'Kemungkinan besar penipuan';

            return $this->result(
                tone: 'red',
                level: 'Tingkat bahaya tinggi',
                title: $title,
                robot: 'danger',
                robotMessage: 'Gawat! Ini tampak seperti penipuan. Jangan diklik, jangan diunduh, dan jangan ditransfer.',
                reasons: $hits,
                actions: [
                    'Jangan klik tautan, buka file, atau transfer uang.',
                    'Jika sudah memberi data, ikuti langkah di tab Bertindak.',
                    'Bagikan peringatan ke keluarga.',
                ],
                draft: "PERINGATAN: {$title}. Jangan klik tautan, buka file APK, atau transfer uang dari pesan seperti ini. Bagikan ke yang lain agar tidak tertipu.",
            );
        }

        if ($score >= 2) {
            $title = 'Ada beberapa tanda mencurigakan';

            return $this->result(
                tone: 'amber',
                level: 'Perlu waspada',
                title: $title,
                robot: 'idle',
                robotMessage: 'Ada beberapa tanda yang perlu diperhatikan. Verifikasi dulu sebelum bertindak.',
                reasons: $hits,
                actions: [
                    'Verifikasi pengirim lewat nomor atau kanal resmi yang kamu cari sendiri.',
                    'Jangan memberi OTP, PIN, atau kata sandi kepada siapa pun.',
                ],
                draft: "HATI-HATI: {$title}. Verifikasi dulu sebelum mengikuti instruksi dari pesan ini.",
            );
        }

        return $this->result(
            tone: 'green',
            level: 'Risiko rendah',
            title: 'Tidak ada pola penipuan yang jelas',
            robot: 'safe',
            robotMessage: 'Tampak aman. Saya tidak menemukan pola penipuan yang jelas, tetapi tetap waspada ya.',
            reasons: $hits,
            actions: ['Tetap waspada dan jangan berbagi kode OTP.'],
            draft: 'Tetap waspada dan verifikasi setiap pesan sebelum bertindak.',
        );
    }

    private function result(
        string $tone,
        string $level,
        string $title,
        string $robot,
        string $robotMessage,
        array $reasons,
        array $actions,
        string $draft,
    ): array {
        return [
            'tone' => $tone,
            'level' => $level,
            'title' => $title,
            'robot' => $robot,
            'robot_message' => $robotMessage,
            'reasons' => $reasons,
            'actions' => $actions,
            'draft' => $draft,
        ];
    }
}
