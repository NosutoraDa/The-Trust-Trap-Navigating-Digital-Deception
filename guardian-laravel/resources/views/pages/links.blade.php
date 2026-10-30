@extends('layouts.app')

@section('title', 'Links')
@section('active', 'links')
@section('robot-mode', 'scan')
@section('robot-message', 'Masukkan satu tautan yang boleh dibagikan untuk memeriksanya dengan VirusTotal.')

@section('content')
    <div class="card">
        <div class="tool-heading">
            <span class="tool-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
            <div>
                <h2>Periksa tautan</h2>
                <p>Periksa satu tautan publik menggunakan VirusTotal.</p>
            </div>
        </div>
        <label for="link-input">Tautan HTTP atau HTTPS</label>
        <input id="link-input" type="url" maxlength="2048" placeholder="https://example.com" autocomplete="url" required>

        <label class="external-check">
            <input id="virustotal-consent" type="checkbox">
            <span>
                <strong>Saya setuju mengirim tautan ini ke VirusTotal</strong>
                <small>Tautan yang dikirim atau diperiksa dapat masuk ke basis data VirusTotal. Jangan kirim tautan pribadi, rahasia, atau sekali pakai.</small>
            </span>
        </label>

        <div class="row">
            <button type="button" id="check-btn" class="btn-primary">Periksa dengan VirusTotal</button>
            <button type="button" id="clear-btn" class="btn-ghost" title="Bersihkan">Bersihkan</button>
        </div>
        <p class="note">VirusTotal membandingkan tautan dengan berbagai pemindai. Hasil tanpa deteksi bukan jaminan keamanan.</p>
    </div>

    <div id="result" class="card" hidden aria-live="polite">
        <div class="tool-heading">
            <span class="tool-icon"><i class="fas fa-shield-halved" aria-hidden="true"></i></span>
            <div>
                <h2>Hasil VirusTotal</h2>
                <p>Laporan hasil pemeriksaan tautan.</p>
            </div>
        </div>
        <p id="virustotal-message"></p>
        <p id="virustotal-stats" class="muted" hidden></p>
        <button id="virustotal-refresh" class="btn-ghost" type="button" hidden>Perbarui status pemindaian</button>
        <a id="virustotal-link" class="btn-soft" target="_blank" rel="noopener noreferrer" hidden>Buka laporan VirusTotal</a>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('link-input');
    const result = document.getElementById('result');
    const button = document.getElementById('check-btn');
    const refresh = document.getElementById('virustotal-refresh');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pending = null;

    function render(report) {
        document.getElementById('virustotal-message').textContent = report.message;
        const stats = document.getElementById('virustotal-stats');
        if (report.stats) {
            stats.textContent = [
                `Berbahaya: ${report.stats.malicious ?? 0}`,
                `Mencurigakan: ${report.stats.suspicious ?? 0}`,
                `Tidak ditandai berbahaya: ${report.stats.harmless ?? 0}`,
                `Belum terdeteksi: ${report.stats.undetected ?? 0}`,
            ].join(' | ');
            stats.hidden = false;
        } else {
            stats.hidden = true;
        }

        pending = report.status === 'pending' && report.analysis_id && report.url_id
            ? { analysis_id: report.analysis_id, url_id: report.url_id }
            : null;
        refresh.hidden = !pending;
        const reportLink = document.getElementById('virustotal-link');
        reportLink.hidden = !report.report_url;
        if (report.report_url) reportLink.href = report.report_url;
        result.hidden = false;
        Robot.setState(
            report.verdict === 'malicious' ? 'danger' : 'idle',
            report.message
        );
    }

    async function request(url, body) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Pemeriksaan VirusTotal gagal.');
        return data;
    }

    button.addEventListener('click', async () => {
        if (button.disabled) return;
        if (!document.getElementById('virustotal-consent').checked) {
            Robot.setState('idle', 'Setujui pengiriman tautan ke VirusTotal terlebih dahulu.');
            return;
        }
        if (!input.reportValidity()) return;

        button.disabled = true;
        result.hidden = true;
        Robot.setState('scanning', 'Tautan sedang diperiksa oleh VirusTotal...');
        try {
            const data = await request('{{ route('api.links') }}', {
                text: input.value.trim(),
                share_url_with_virustotal: true,
            });
            render(data.virustotal);
        } catch (error) {
            document.getElementById('virustotal-message').textContent = error.message || 'Pemeriksaan VirusTotal gagal.';
            result.hidden = false;
            Robot.setState('idle', error.message || 'Pemeriksaan VirusTotal gagal.');
        } finally {
            button.disabled = false;
        }
    });

    refresh.addEventListener('click', async () => {
        if (!pending || refresh.disabled) return;
        refresh.disabled = true;
        try {
            render(await request('{{ route('api.links.analysis') }}', pending));
        } catch (error) {
            document.getElementById('virustotal-message').textContent = error.message || 'Status VirusTotal tidak dapat diperbarui.';
        } finally {
            refresh.disabled = false;
        }
    });

    document.getElementById('clear-btn').addEventListener('click', () => {
        input.value = '';
        document.getElementById('virustotal-consent').checked = false;
        result.hidden = true;
        pending = null;
        refresh.hidden = true;
        Robot.setState('idle', 'Masukkan satu tautan yang boleh dibagikan untuk memeriksanya dengan VirusTotal.');
    });
})();
</script>
@endpush
