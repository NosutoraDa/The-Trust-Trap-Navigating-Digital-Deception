@php($current = $__env->yieldContent('active'))
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Guardian - @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body data-robot="{{ $__env->yieldContent('robot-mode', 'idle') }}">
<div class="grid-bg"></div>

<header class="topbar">
    <div class="brand">
        <div class="brand-mark"><i class="fas fa-shield-halved"></i></div>
        <div>
            <h1>Guardian</h1>
            <p>Cek, belajar, bertindak</p>
        </div>
    </div>
    <nav class="menu">
        <a href="{{ route('cek') }}" class="menu-item {{ $current === 'cek' ? 'active' : '' }}"><span class="menu-num">01</span>Cek</a>
        <a href="{{ route('belajar') }}" class="menu-item {{ $current === 'belajar' ? 'active' : '' }}"><span class="menu-num">02</span>Belajar</a>
        <a href="{{ route('bertindak') }}" class="menu-item {{ $current === 'bertindak' ? 'active' : '' }}"><span class="menu-num">03</span>Bertindak</a>
        <a href="{{ route('tanya') }}" class="menu-item {{ $current === 'tanya' ? 'active' : '' }}"><span class="menu-num">04</span>Tanya AI</a>
    </nav>
</header>

<main class="layout">
    <x-guardian.robot :message="$__env->yieldContent('robot-message')" />

    <section class="content">
        @yield('content')
    </section>
</main>

<footer class="footer">
    Guardian memberi panduan edukatif dan tidak menjamin keamanan 100%. Data yang kamu masukkan diproses di perangkatmu dan tidak disimpan.
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="{{ asset('js/robot.js') }}"></script>
@stack('scripts')
</body>
</html>
