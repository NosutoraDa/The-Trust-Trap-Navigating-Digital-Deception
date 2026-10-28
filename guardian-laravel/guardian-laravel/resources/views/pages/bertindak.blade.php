@extends('layouts.app')

@section('title', 'Bertindak')
@section('active', 'bertindak')
@section('robot-mode', 'alert')
@section('robot-message', 'Sudah terlanjur? Ikuti langkah ini dulu, satu per satu.')

@section('content')
    <div class="card">
        <h2>Jika kamu sudah terlanjur</h2>
        <ol class="steps">
            <li>Jangan buka file APK atau isi data di tautan tersebut lagi.</li>
            <li>Jika sudah transfer atau memberi OTP/PIN, hubungi call center resmi bank Anda untuk pemblokiran secepatnya.</li>
            <li>Ganti kata sandi akun penting dan aktifkan autentikasi dua langkah (2FA).</li>
            <li>Cek nomor rekening penerima di <strong>cekrekening.id</strong> bila tersedia.</li>
            <li>Laporkan ke <strong>patrolisiber.id</strong> atau kepolisian setempat dengan tangkapan layar sebagai bukti.</li>
            <li>Untuk investasi atau layanan keuangan ilegal, laporkan ke OJK melalui <strong>157</strong>.</li>
        </ol>
        <p class="note">Verifikasi nomor dan situs di atas sendiri sebelum menghubunginya.</p>
    </div>

    <div class="card">
        <h2>Peringatan untuk keluarga</h2>
        <p class="muted">Draf ini diambil dari hasil pemeriksaan terakhir di tab Cek.</p>
        <div id="draft" class="draft">{{ $draft }}</div>
        <button type="button" id="copy-btn" class="btn-soft"><i class="fas fa-copy"></i> Salin draf</button>
        <span id="copy-status" class="copy-status"></span>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const copyButton = document.getElementById('copy-btn');
    const status = document.getElementById('copy-status');

    copyButton.addEventListener('click', () => {
        const text = document.getElementById('draft').textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            status.textContent = 'Tersalin!';
            setTimeout(() => status.textContent = '', 2000);
        });
    });
})();
</script>
@endpush
