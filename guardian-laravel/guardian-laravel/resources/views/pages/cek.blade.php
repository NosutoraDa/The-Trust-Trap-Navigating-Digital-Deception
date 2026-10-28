@extends('layouts.app')

@section('title', 'Cek')
@section('active', 'cek')
@section('robot-mode', 'scan')
@section('robot-message', 'Tempel pesan, tautan, atau nama file yang mencurigakan, lalu saya periksa.')

@section('content')
    <div class="card">
        <h2>Periksa sesuatu</h2>
        <textarea id="user-input" rows="5" placeholder="Contoh: Selamat, Anda menang undian 50 juta. Segera transfer biaya admin ke QRIS ini."></textarea>

        <div class="chips">
            <span class="muted" style="margin:0">Coba contoh:</span>
            @foreach ($examples as $example)
                <button type="button" class="chip" data-example="{{ $example['text'] }}">{{ $example['label'] }}</button>
            @endforeach
        </div>

        <div class="row">
            <button type="button" id="check-btn" class="btn-primary"><i class="fas fa-magnifying-glass"></i> Periksa</button>
            <button type="button" id="clear-btn" class="btn-ghost" title="Bersihkan"><i class="fas fa-rotate-left"></i></button>
        </div>
        <p class="note">Pemeriksaan ini memakai aturan pola sederhana. Hasil "tidak terdeteksi" belum tentu aman. Selalu verifikasi lewat kanal resmi.</p>
    </div>

    <div id="result" class="card" hidden>
        <div class="result-head">
            <div id="res-icon" class="result-icon"></div>
            <div>
                <span id="res-level" class="badge"></span>
                <h2 id="res-title"></h2>
            </div>
        </div>
        <h3>Tanda yang terdeteksi</h3>
        <ul id="res-reasons" class="list"></ul>
        <h3>Yang harus dilakukan sekarang</h3>
        <ul id="res-actions" class="list ok"></ul>
        <a href="{{ route('bertindak') }}" class="btn-soft" style="text-decoration:none;display:inline-block">Lanjut ke Bertindak</a>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('user-input');
    const result = document.getElementById('result');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const TONE_ICON = { red: 'fa-triangle-exclamation', amber: 'fa-circle-exclamation', green: 'fa-shield-halved' };
    const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

    function listItem(icon, text) {
        const li = document.createElement('li');
        const i = document.createElement('i');
        i.className = 'fas ' + icon;
        const span = document.createElement('span');
        span.textContent = text;
        li.append(i, span);
        return li;
    }

    function render(data) {
        const icon = document.getElementById('res-icon');
        icon.className = 'result-icon tone-' + data.tone;
        icon.innerHTML = `<i class="fas ${TONE_ICON[data.tone]}"></i>`;

        const level = document.getElementById('res-level');
        level.className = 'badge tone-' + data.tone;
        level.textContent = data.level;
        document.getElementById('res-title').textContent = data.title;

        const reasons = document.getElementById('res-reasons');
        reasons.replaceChildren();
        if (data.reasons.length === 0) {
            reasons.append(listItem('fa-circle-info', 'Tidak ada pola yang cocok. Tetap verifikasi pengirim sebelum bertindak.'));
        } else {
            data.reasons.forEach(reason => reasons.append(listItem('fa-angle-right', reason)));
        }

        const actions = document.getElementById('res-actions');
        actions.replaceChildren(...data.actions.map(action => listItem('fa-check', action)));

        result.hidden = false;
        Robot.setState(data.robot, data.robot_message);
    }

    document.querySelectorAll('[data-example]').forEach(chip => {
        chip.addEventListener('click', () => {
            input.value = chip.dataset.example;
        });
    });

    document.getElementById('clear-btn').addEventListener('click', () => {
        input.value = '';
        result.hidden = true;
        Robot.setState('idle', 'Tempel pesan, tautan, atau nama file yang mencurigakan, lalu saya periksa.');
    });

    document.getElementById('check-btn').addEventListener('click', async () => {
        const text = input.value.trim();
        if (!text) {
            Robot.setState('idle', 'Tempel dulu pesan, tautan, atau nama file yang ingin diperiksa ya.');
            return;
        }

        Robot.setState('scanning', 'Mohon tunggu, saya sedang memeriksa tanda-tanda bahayanya...');
        result.hidden = true;

        try {
            const [response] = await Promise.all([
                fetch('{{ route('api.cek') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ text }),
                }),
                sleep(900),
            ]);

            if (!response.ok) throw new Error('HTTP ' + response.status);
            render(await response.json());
        } catch (error) {
            Robot.setState('idle', 'Maaf, pemeriksaan gagal. Coba lagi sebentar lagi.');
        }
    });
})();
</script>
@endpush
