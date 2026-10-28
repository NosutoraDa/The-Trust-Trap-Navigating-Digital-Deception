@extends('layouts.app')

@section('title', 'Belajar')
@section('active', 'belajar')
@section('robot-mode', 'study')
@section('robot-message', 'Pilih respons yang menurutmu paling aman. Saya akan menjelaskan setiap pilihan.')

@section('content')
    <div class="card">
        <h2>Latihan skenario</h2>
        <p class="muted">Baca skenarionya, lalu pilih satu respons.</p>
    </div>

    @foreach ($scenarios as $number => $scenario)
        <div class="card">
            <h2>{{ $number + 1 }}. {{ $scenario['title'] }}</h2>
            <div class="scenario-msg">{{ $scenario['msg'] }}</div>

            @foreach ($scenario['choices'] as $choice)
                <button type="button"
                        class="choice"
                        data-ok="{{ $choice['ok'] ? '1' : '0' }}"
                        data-why="{{ $choice['why'] }}">{{ $choice['text'] }}</button>
            @endforeach

            <div class="feedback" hidden></div>
        </div>
    @endforeach
@endsection

@push('scripts')
<script>
document.querySelectorAll('.choice').forEach(button => {
    button.addEventListener('click', () => {
        const ok = button.dataset.ok === '1';
        const feedback = button.closest('.card').querySelector('.feedback');

        feedback.className = 'feedback ' + (ok ? 'good' : 'bad');
        feedback.textContent = (ok ? 'Benar. ' : 'Kurang tepat. ') + button.dataset.why;
        feedback.hidden = false;

        Robot.setState(
            ok ? 'safe' : 'danger',
            ok ? 'Pilihan yang tepat!' : 'Hati-hati, ada yang perlu dipikirkan lagi.'
        );
    });
});
</script>
@endpush
