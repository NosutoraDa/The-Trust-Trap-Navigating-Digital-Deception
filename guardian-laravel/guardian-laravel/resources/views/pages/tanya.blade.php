@extends('layouts.app')

@section('title', 'Tanya AI')
@section('active', 'tanya')
@section('robot-mode', 'chat')
@section('robot-message', 'Tanyakan apa saja seputar penipuan digital dan keamanan siber.')

@section('content')
    <div class="card">
        <h2>Tanya seputar keamanan siber</h2>
        <p class="warn-note">Mode demo: jawaban dari config/guardian.php. Untuk jawaban dinamis, sambungkan AskController ke Claude API.</p>

        <div id="chat-log" class="chat-log">
            <div class="msg bot">Halo! Tanyakan misalnya "apa itu OTP?" atau "kenapa APK berbahaya?"</div>
        </div>

        <form id="chat-form" class="row">
            <input id="chat-input" type="text" placeholder="Tulis pertanyaanmu..." autocomplete="off">
            <button class="btn-primary" type="submit">Kirim</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const log = document.getElementById('chat-log');
    const input = document.getElementById('chat-input');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function addMsg(text, who) {
        const div = document.createElement('div');
        div.className = 'msg ' + who;
        div.textContent = text;
        log.appendChild(div);
        log.scrollTop = log.scrollHeight;
    }

    document.getElementById('chat-form').addEventListener('submit', async event => {
        event.preventDefault();
        const question = input.value.trim();
        if (!question) return;

        addMsg(question, 'user');
        input.value = '';
        Robot.pulse(); // robot "types" while the answer loads

        try {
            const response = await fetch('{{ route('api.tanya') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ question }),
            });

            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();
            addMsg(data.answer, 'bot');
        } catch (error) {
            addMsg('Maaf, jawaban belum bisa diambil. Coba lagi sebentar lagi.', 'bot');
        }
    });
})();
</script>
@endpush
