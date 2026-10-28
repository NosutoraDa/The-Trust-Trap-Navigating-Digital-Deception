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
            'text' => 'Lowongan kerja paruh waktu, komisi besar. Untuk daftar, transfer biaya admin 150rb ke QRIS atas nama Budi Santoso.',
        ],
    ],

    'scenarios' => [
        [
            'title' => 'Chat "rekruter" di WhatsApp',
            'msg' => '"Selamat! Anda lolos seleksi admin online. Like 20 video, dapat Rp150.000 per hari. Untuk aktivasi, isi saldo Rp200.000 dulu ya."',
            'choices' => [
                ['text' => 'Transfer karena nominalnya kecil dan sudah lolos seleksi', 'ok' => false, 'why' => 'Pekerjaan sah tidak meminta setoran di awal. Ini pola umum penipuan kerja paruh waktu.'],
                ['text' => 'Cek perusahaan lewat kontak resmi di situsnya', 'ok' => true, 'why' => 'Tepat. Verifikasi lewat kanal yang kamu cari sendiri, bukan dari pesan tersebut.'],
                ['text' => 'Tanya dulu lebih jauh, lalu tetap transfer jika meyakinkan', 'ok' => false, 'why' => 'Penipu akan terus meyakinkan. Jangan ambil keputusan transfer saat ada tekanan.'],
            ],
        ],
        [
            'title' => 'SMS "paket tertahan"',
            'msg' => 'Paket Anda tertahan karena alamat tidak lengkap. Konfirmasi di: jne-resi-update.xyz/konfirmasi',
            'choices' => [
                ['text' => 'Klik tautan dan isi alamat agar paket bisa dikirim', 'ok' => false, 'why' => 'Domain itu bukan situs resmi JNE. Tautan semacam ini dipakai untuk mencuri data.'],
                ['text' => 'Cek resi langsung di aplikasi atau situs resmi kurir', 'ok' => true, 'why' => 'Tepat. Cek sendiri lewat jalur resmi, bukan lewat tautan di SMS.'],
                ['text' => 'Balas dan tanya siapa pengirimnya', 'ok' => false, 'why' => 'Membalas memberi tahu penipu bahwa nomormu aktif dan bisa memicu pesan berikutnya.'],
            ],
        ],
        [
            'title' => 'Telepon "petugas bank"',
            'msg' => 'Penelepon mengaku dari bank: "Kartu Anda akan diblokir. Sebutkan kode OTP yang baru kami kirim agar bisa diverifikasi."',
            'choices' => [
                ['text' => 'Sebutkan OTP agar kartu tidak diblokir', 'ok' => false, 'why' => 'Bank tidak pernah meminta OTP. Jangan pernah memberikannya kepada siapa pun.'],
                ['text' => 'Tutup telepon, lalu hubungi nomor di belakang kartu', 'ok' => true, 'why' => 'Tepat. Hubungi nomor resmi untuk memastikan status kartumu.'],
                ['text' => 'Minta nama pegawainya lalu lanjutkan percakapan', 'ok' => false, 'why' => 'Nama dan nomor pegawai bisa dipalsukan. Tetap gunakan kanal resmi.'],
            ],
        ],
    ],

    // Demo answers for Tanya AI. Replace AskController logic with a real AI call later.
    'qa' => [
        ['keywords' => ['otp'], 'answer' => 'OTP adalah kode sekali pakai untuk verifikasi. Jangan pernah membagikannya, termasuk kepada orang yang mengaku dari bank.'],
        ['keywords' => ['apk'], 'answer' => 'File APK dipasang dari luar toko aplikasi resmi, sehingga bisa membaca SMS dan membobol OTP. Pasang aplikasi hanya dari Play Store atau App Store.'],
        ['keywords' => ['2fa', 'dua langkah'], 'answer' => 'Autentikasi dua langkah menambahkan lapisan kedua, misalnya kode dari aplikasi autentikator, sehingga kata sandi saja tidak cukup untuk masuk.'],
        ['keywords' => ['phishing'], 'answer' => 'Phishing adalah tautan atau pesan palsu yang meniru layanan resmi untuk mencuri data. Periksa domainnya dan jangan memasukkan data lewat tautan dari pesan tak dikenal.'],
        ['keywords' => ['qris'], 'answer' => 'QRIS resmi ditujukan ke merchant terdaftar. Jika diminta scan QRIS untuk "biaya admin" atau "aktivasi", itu tanda penipuan.'],
    ],

    'qa_fallback' => 'Untuk demo ini, saya baru bisa menjawab topik OTP, APK, 2FA, phishing, dan QRIS. Pertanyaan lain akan dijawab setelah AI tersambung.',

];
