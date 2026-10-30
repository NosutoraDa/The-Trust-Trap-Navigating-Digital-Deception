<?php

return [

    'examples' => [
        [
            'label' => 'Link phishing',
            'text' => 'https://hadiah-bank-mandiri.blogspot.com/login-verifikasi?token=99283 Akun Anda akan diblokir hari ini, segera verifikasi dan masukkan kata sandi serta OTP.',
        ],
        [
            'label' => 'Undangan APK',
            'text' => 'Halo Kak, ini undangan pernikahan digital kami. Silakan instal SuratUndangan.apk ini ya.',
        ],
        [
            'label' => 'Lowongan kerja',
            'text' => 'Lowongan kerja paruh waktu, komisi besar. Untuk daftar, transfer biaya admin 150rb sekarang.',
        ],
    ],

    'ai' => [
        'endpoint' => env('HUGGINGFACE_API_URL', 'https://router.huggingface.co/v1/chat/completions'),
        'model' => env('HUGGINGFACE_MODEL', 'Qwen/Qwen2.5-7B-Instruct'),
        'token' => env('HUGGINGFACE_API_TOKEN'),
    ],

    'virustotal' => [
        'api_key' => env('VIRUSTOTAL_API_KEY'),
    ],

    'metadefender' => [
        'api_key' => env('OPSWAT_API_KEY'),
    ],

];
