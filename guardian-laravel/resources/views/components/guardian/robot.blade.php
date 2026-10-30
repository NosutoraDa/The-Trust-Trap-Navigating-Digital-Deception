@props(['message' => ''])

<aside class="robot-panel" aria-label="Asisten Si Guardian">
    <div id="mascot-stage" class="mascot-stage">
        <div id="radial-dial-container" class="radial-dial-container">
            <div id="arc-track-svg" class="arc-track-svg" aria-hidden="true">
                <svg viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="250" cy="250" r="210" stroke="rgba(0, 229, 255, 0.35)" stroke-width="2" stroke-dasharray="3 7"/>
                    <circle cx="250" cy="250" r="180" stroke="rgba(255, 255, 255, 0.15)" stroke-width="1.5" stroke-dasharray="2 6"/>
                    <circle cx="250" cy="250" r="240" stroke="rgba(0, 229, 255, 0.15)" stroke-width="1" stroke-dasharray="4 8"/>
                    <path class="arc-menu-highlight" d="M 53 178 A 210 210 0 0 1 447 178" stroke="#00E5FF" stroke-width="3" stroke-linecap="round" stroke-dasharray="6 4" opacity="0.85"/>
                    <line x1="103" y1="165" x2="42" y2="130" stroke="#00E5FF" stroke-width="1.5" stroke-dasharray="2 4" opacity="0.6"/>
                    <line x1="250" y1="80" x2="250" y2="10" stroke="#10B981" stroke-width="1.5" stroke-dasharray="2 4" opacity="0.6"/>
                    <line x1="397" y1="165" x2="458" y2="130" stroke="#FBBF24" stroke-width="1.5" stroke-dasharray="2 4" opacity="0.6"/>
                    <circle cx="68" cy="145" r="14" stroke="#00E5FF" stroke-width="1.5" stroke-dasharray="2 3" opacity="0.8"/>
                    <circle cx="68" cy="145" r="5" fill="#00E5FF"/>
                    <circle cx="250" cy="40" r="16" stroke="#10B981" stroke-width="1.5" stroke-dasharray="2 3" opacity="0.8"/>
                    <circle cx="250" cy="40" r="6" fill="#10B981"/>
                    <circle cx="432" cy="145" r="14" stroke="#FBBF24" stroke-width="1.5" stroke-dasharray="2 3" opacity="0.8"/>
                    <circle cx="432" cy="145" r="5" fill="#FBBF24"/>
                </svg>
            </div>

            <a href="{{ route('tanya') }}" class="arc-item radial-chat {{ request()->routeIs('tanya') ? 'current' : '' }}" style="--angle: -60deg; --menu-delay: .08s;" aria-current="{{ request()->routeIs('tanya') ? 'page' : 'false' }}">
                <span class="arc-item-content">
                    <span class="radial-icon radial-cyan"><i class="fas fa-comment-dots" aria-hidden="true"></i></span>
                    <span class="radial-label"><strong>Chatbot</strong><small>Asisten keamanan AI</small></span>
                </span>
            </a>
            <a href="{{ route('files') }}" class="arc-item radial-files {{ request()->routeIs('files') ? 'current' : '' }}" style="--angle: 0deg; --menu-delay: .18s;" aria-current="{{ request()->routeIs('files') ? 'page' : 'false' }}">
                <span class="arc-item-content">
                    <span class="radial-icon radial-green"><i class="fas fa-file-shield" aria-hidden="true"></i></span>
                    <span class="radial-label"><strong>Files</strong><small>Pindai file dengan OPSWAT</small></span>
                </span>
            </a>
            <a href="{{ route('links') }}" class="arc-item radial-links {{ request()->routeIs('links') ? 'current' : '' }}" style="--angle: 60deg; --menu-delay: .28s;" aria-current="{{ request()->routeIs('links') ? 'page' : 'false' }}">
                <span class="arc-item-content">
                    <span class="radial-icon radial-amber"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <span class="radial-label"><strong>Links</strong><small>Periksa tautan dengan VT</small></span>
                </span>
            </a>
        </div>

        <button id="mascot-toggle-menu" class="mascot-button" type="button" aria-label="Buka atau tutup menu radial" aria-controls="radial-dial-container" aria-expanded="true">
        <div id="robot-glow-aura" class="robot-glow" aria-hidden="true"></div>
        <div class="robot-orbit robot-orbit-outer" aria-hidden="true"></div>
        <div class="robot-orbit robot-orbit-inner" aria-hidden="true"></div>
        <span class="mascot-float">
        <svg id="robot-svg" class="robot-svg" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Maskot robot Si Guardian">
            <defs>
                <filter id="cyan-glow" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur stdDeviation="3" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                </filter>
            </defs>
            <path d="M 38 76 C 30 40, 72 38, 68 76 Z" fill="#E2E8F0" stroke="#94A3B8" stroke-width="3.5"/>
            <path d="M 162 76 C 170 40, 128 38, 132 76 Z" fill="#E2E8F0" stroke="#94A3B8" stroke-width="3.5"/>
            <rect x="35" y="48" width="130" height="108" rx="42" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="4"/>
            <rect x="47" y="58" width="106" height="88" rx="30" fill="#090D16"/>
            <path d="M 55 66 C 80 61, 120 61, 145 66 C 130 76, 70 76, 55 66 Z" fill="white" opacity="0.12"/>
            <g filter="url(#cyan-glow)">
                <rect id="left-eye" x="65" y="76" width="22" height="28" rx="8" fill="#00E5FF"/>
                <rect id="right-eye" x="113" y="76" width="22" height="28" rx="8" fill="#00E5FF"/>
                <path id="smooth-mouth" d="M 82 116 Q 100 130 118 116" stroke="#00E5FF" stroke-width="5" stroke-linecap="round" fill="none"/>
            </g>
        </svg>
        </span>
        </button>
    </div>
    <div id="speech" class="speech glow-blue">
        <span class="speech-kicker"><i class="fas fa-satellite-dish" aria-hidden="true"></i> GUARDIAN ONLINE</span>
        <p id="speech-text">{{ $message }}</p>
    </div>
</aside>
