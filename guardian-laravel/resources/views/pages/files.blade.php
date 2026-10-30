@extends('layouts.app')

@section('title', 'Files')
@section('active', 'files')
@section('robot-mode', 'scan')
@section('robot-message', 'Unggah hanya file yang boleh dibagikan untuk pemeriksaan eksternal.')

@section('content')
    <div class="card">
        <div class="tool-heading">
            <span class="tool-icon"><i class="fas fa-file-shield" aria-hidden="true"></i></span>
            <div>
                <h2>Periksa file</h2>
                <p>Pindai file secara eksternal dengan OPSWAT MetaDefender.</p>
            </div>
        </div>
        <p class="warn-note">File dikirim ke OPSWAT MetaDefender untuk dipindai, bukan ke chatbot. Jangan unggah dokumen pribadi, rahasia, atau file milik orang lain.</p>

        <form id="file-form">
            <label for="file-input">Pilih file (maksimal 10 MB)</label>
            <input id="file-input" name="file" type="file" required>

            <label class="external-check">
                <input id="file-consent" name="share_file_with_opswat" type="checkbox" value="1" required>
                <span>
                    <strong>Saya setuju mengirim file ini ke OPSWAT untuk diperiksa</strong>
                    <small>File akan dikirim untuk analisis malware. Aplikasi ini tidak menyimpan file secara permanen. Jangan kirim file sensitif.</small>
                </span>
            </label>

            <div class="row">
                <button id="file-submit" class="btn-primary" type="submit">Pindai file</button>
                <button id="file-clear" class="btn-ghost" type="reset">Bersihkan</button>
            </div>
        </form>

        <p class="note">Hasil pemindaian dapat keliru atau belum mengenali ancaman baru. Jangan membuka file hanya karena hasilnya bersih.</p>
    </div>

    <div id="file-result" class="card" hidden aria-live="polite">
        <div class="tool-heading">
            <span class="tool-icon"><i class="fas fa-shield-halved" aria-hidden="true"></i></span>
            <div>
                <h2>Hasil pemindaian file</h2>
                <p>Laporan hasil pemeriksaan file.</p>
            </div>
        </div>
        <p id="file-message"></p>
        <p id="file-stats" class="muted" hidden></p>
        <button id="file-refresh" class="btn-ghost" type="button" hidden>Perbarui status</button>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('file-form');
    const submit = document.getElementById('file-submit');
    const result = document.getElementById('file-result');
    const message = document.getElementById('file-message');
    const stats = document.getElementById('file-stats');
    const refresh = document.getElementById('file-refresh');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pending = false;

    function render(data) {
        message.textContent = data.message;
        if (data.detections !== undefined && data.engines !== undefined) {
            stats.textContent = `Deteksi: ${data.detections} dari ${data.engines} mesin pemindai`;
            stats.hidden = false;
        } else {
            stats.hidden = true;
        }
        pending = data.status === 'pending';
        refresh.hidden = !pending;
        result.hidden = false;
        Robot.setState(data.verdict === 'detected' ? 'danger' : 'idle', data.message);
    }

    async function send(url, body, isForm) {
        const headers = { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
        if (!isForm) headers['Content-Type'] = 'application/json';
        const response = await fetch(url, {
            method: 'POST',
            headers,
            body: isForm ? body : undefined,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Pemindaian file gagal.');
        return data;
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled || !form.reportValidity()) return;

        const file = document.getElementById('file-input').files[0];
        if (file.size > 10 * 1024 * 1024) {
            message.textContent = 'Ukuran file melebihi batas 10 MB.';
            result.hidden = false;
            return;
        }

        const body = new FormData(form);
        body.set('share_file_with_opswat', '1');
        submit.disabled = true;
        refresh.hidden = true;
        result.hidden = true;
        Robot.setState('scanning', 'File sedang dikirim dengan persetujuanmu untuk dipindai...');

        try {
            render(await send('{{ route('api.files') }}', body, true));
        } catch (error) {
            message.textContent = error.message || 'Pemindaian file gagal.';
            result.hidden = false;
            Robot.setState('idle', message.textContent);
        } finally {
            submit.disabled = false;
        }
    });

    refresh.addEventListener('click', async () => {
        if (!pending || refresh.disabled) return;
        refresh.disabled = true;
        try {
            render(await send('{{ route('api.files.analysis') }}', null, false));
        } catch (error) {
            message.textContent = error.message || 'Status pemindaian tidak dapat diperbarui.';
        } finally {
            refresh.disabled = false;
        }
    });

    form.addEventListener('reset', () => {
        result.hidden = true;
        pending = false;
        refresh.hidden = true;
    });
})();
</script>
@endpush
