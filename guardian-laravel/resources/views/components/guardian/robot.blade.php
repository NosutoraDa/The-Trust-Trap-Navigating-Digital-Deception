@props(['message' => ''])

<aside class="robot-panel">
    <div id="speech" class="speech glow-blue">
        <p id="speech-text">{{ $message }}</p>
    </div>
    <div id="avatar"></div>
    <div class="status"><span id="status-text">Mode siaga</span></div>
</aside>
