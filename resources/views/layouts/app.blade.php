<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f172a">

        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('apple-touch-icon.png') }}">

        <title>{{ config('app.name', 'PayTrade') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            }
            /* Scrollbar dark */
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #0f172a; }
            ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: #475569; }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-900 text-slate-100">
        <div class="flex h-screen overflow-hidden">

            {{-- Sidebar (lewa kolumna) --}}
            @include('layouts.sidebar')

            {{-- Mobile sidebar overlay (hamburger menu) --}}
            <div id="mobile-sidebar-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"
                 onclick="document.getElementById('mobile-sidebar').classList.add('hidden');this.classList.add('hidden')"></div>
            <div id="mobile-sidebar" class="fixed left-0 top-0 bottom-0 z-50 hidden lg:hidden">
                @include('layouts.sidebar')
            </div>

            {{-- Główna kolumna --}}
            <div class="flex-1 flex flex-col overflow-hidden">

                {{-- Topbar --}}
                <header class="bg-slate-950 border-b border-slate-800 px-4 sm:px-6 py-3 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Mobile hamburger --}}
                        <button type="button"
                                onclick="document.getElementById('mobile-sidebar').classList.remove('hidden');document.getElementById('mobile-sidebar-backdrop').classList.remove('hidden')"
                                class="lg:hidden text-slate-300 hover:text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        @isset($header)
                            <div class="text-base sm:text-lg font-semibold text-white truncate">{{ $header }}</div>
                        @endisset
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        @auth
                            <div class="text-sm text-slate-300 hidden sm:block">
                                Witaj, <strong class="text-white">{{ Auth::user()->name }}</strong>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow-md hover:shadow-lg transition">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </a>
                        @endauth
                    </div>
                </header>

                {{-- Główna zawartość --}}
                <main class="flex-1 overflow-y-auto bg-slate-900">
                    <div class="p-4 sm:p-6">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        {{-- 🎤 Voice Input — mikrofon TYLKO na stronach z formularzami --}}
        @php
            $showVoiceMic = request()->routeIs(
                'vehicles.create', 'vehicles.edit', 'vehicles.store', 'vehicles.update',
                'vehicles.sale.edit', 'vehicles.sale.update',
                'contractors.create', 'contractors.edit',
                'settings.edit'
            );
        @endphp
        @if($showVoiceMic)
        <div id="voice-widget" style="position:fixed;bottom:16px;right:16px;z-index:9999;font-family:system-ui,sans-serif;opacity:0.5;transition:opacity .2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.5">
            <button id="voice-btn" type="button"
                    title="Kliknij w pole tekstowe, potem ten przycisk i mów (PL/EN)"
                    style="width:44px;height:44px;border-radius:50%;border:none;background:#4f46e5;color:white;font-size:20px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.2);transition:all .2s;">
                🎤
            </button>
            <button id="voice-hide" type="button" title="Ukryj mikrofon"
                    onclick="document.getElementById('voice-widget').style.display='none';sessionStorage.setItem('voice-hidden','1')"
                    style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;border:none;background:#dc2626;color:white;font-size:11px;cursor:pointer;line-height:1;">✕</button>
            <div id="voice-status" style="display:none;position:absolute;bottom:54px;right:0;background:white;padding:8px 14px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:13px;white-space:nowrap;border:2px solid #4f46e5;color:#0f172a;"></div>
            <div id="voice-lang" style="position:absolute;bottom:0;right:50px;background:white;border:1px solid #d1d5db;border-radius:6px;font-size:11px;padding:1px 5px;cursor:pointer;user-select:none;color:#0f172a;">PL</div>
        </div>
        <script>
            if (sessionStorage.getItem('voice-hidden') === '1') {
                document.getElementById('voice-widget').style.display = 'none';
            }
        </script>
        <script>
        (function () {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            const btn = document.getElementById('voice-btn');
            const status = document.getElementById('voice-status');
            const langBtn = document.getElementById('voice-lang');
            if (!SR) { document.getElementById('voice-widget').style.display = 'none'; return; }

            let lastField = null;
            document.addEventListener('focusin', (e) => {
                const el = e.target;
                if (el.matches('input[type="text"],input[type="search"],input:not([type]),textarea,input[type="email"],input[type="tel"],input[type="number"]')) {
                    lastField = el;
                }
            });

            let lang = localStorage.getItem('voice-lang') || 'pl-PL';
            const setLang = (l) => { lang = l; localStorage.setItem('voice-lang', l); langBtn.textContent = l === 'pl-PL' ? 'PL' : 'EN'; };
            setLang(lang);
            langBtn.addEventListener('click', () => setLang(lang === 'pl-PL' ? 'en-IE' : 'pl-PL'));

            let recognition = null, listening = false;
            const showStatus = (msg, color = '#4f46e5') => {
                status.textContent = msg;
                status.style.borderColor = color;
                status.style.color = color;
                status.style.display = 'block';
                clearTimeout(status._hideTimer);
                status._hideTimer = setTimeout(() => { status.style.display = 'none'; }, 4000);
            };

            btn.addEventListener('click', () => {
                if (listening) { recognition?.stop(); return; }
                if (!lastField) { showStatus('Najpierw kliknij w pole tekstowe ↑', '#dc2626'); return; }
                recognition = new SR();
                recognition.lang = lang;
                recognition.interimResults = true;
                recognition.continuous = false;
                let finalTranscript = '';
                const originalValue = lastField.value;
                recognition.onstart = () => { listening = true; btn.textContent = '🔴'; btn.style.background = '#dc2626'; showStatus('🎙️ Słucham...'); };
                recognition.onresult = (e) => {
                    let interim = '';
                    for (let i = e.resultIndex; i < e.results.length; i++) {
                        const t = e.results[i][0].transcript;
                        if (e.results[i].isFinal) finalTranscript += t; else interim += t;
                    }
                    const combined = (finalTranscript + interim).trim();
                    if (lastField.tagName === 'TEXTAREA') lastField.value = (originalValue ? originalValue + ' ' : '') + combined;
                    else lastField.value = combined;
                    lastField.dispatchEvent(new Event('input', { bubbles: true }));
                };
                recognition.onerror = (e) => showStatus('⚠️ ' + e.error, '#dc2626');
                recognition.onend = () => { listening = false; btn.textContent = '🎤'; btn.style.background = '#4f46e5'; if (finalTranscript) showStatus('✓ Zapisano', '#16a34a'); };
                recognition.start();
            });
        })();
        </script>
        @endif
    </body>
</html>
