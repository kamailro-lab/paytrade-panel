<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4338ca">

        <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('apple-touch-icon.png') }}">

        <title>{{ config('app.name', 'Paytrade') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="pb-24">
                {{ $slot }}
            </main>
        </div>

        {{-- 🎤 Voice Input — mikrofon (mniejszy, semi-transparent gdy idle) --}}
        <div id="voice-widget" style="position:fixed;bottom:16px;right:16px;z-index:9999;font-family:system-ui,sans-serif;opacity:0.6;transition:opacity .2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.6">
            <button id="voice-btn" type="button"
                    title="Kliknij w pole tekstowe, potem ten przycisk i mów (PL/EN)"
                    style="width:48px;height:48px;border-radius:50%;border:none;background:#4f46e5;color:white;font-size:22px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.2);transition:all .2s;">
                🎤
            </button>
            <div id="voice-status" style="display:none;position:absolute;bottom:58px;right:0;background:white;padding:8px 14px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:13px;white-space:nowrap;border:2px solid #4f46e5;"></div>
            <div id="voice-lang" style="position:absolute;bottom:0;right:54px;background:white;border:1px solid #d1d5db;border-radius:6px;font-size:11px;padding:1px 5px;cursor:pointer;user-select:none;">PL</div>
        </div>

        <script>
        (function () {
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            const btn = document.getElementById('voice-btn');
            const status = document.getElementById('voice-status');
            const langBtn = document.getElementById('voice-lang');

            if (!SR) {
                // Brave / Firefox czasem blokują Web Speech API - ukryj widget
                document.getElementById('voice-widget').style.display = 'none';
                console.warn('Web Speech API niedostępne. W Brave: Shields → Site Settings → "Speech recognition" → Allow');
                return;
            }

            // Track last focused text input
            let lastField = null;
            document.addEventListener('focusin', (e) => {
                const el = e.target;
                if (el.matches('input[type="text"],input[type="search"],input:not([type]),textarea,input[type="email"],input[type="tel"],input[type="number"]')) {
                    lastField = el;
                }
            });

            // Language toggle PL/EN
            let lang = localStorage.getItem('voice-lang') || 'pl-PL';
            const setLang = (l) => {
                lang = l;
                localStorage.setItem('voice-lang', l);
                langBtn.textContent = l === 'pl-PL' ? 'PL' : 'EN';
            };
            setLang(lang);
            langBtn.addEventListener('click', () => setLang(lang === 'pl-PL' ? 'en-IE' : 'pl-PL'));

            let recognition = null;
            let listening = false;

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
                if (!lastField) {
                    showStatus('Najpierw kliknij w pole tekstowe ↑', '#dc2626');
                    return;
                }

                recognition = new SR();
                recognition.lang = lang;
                recognition.interimResults = true;
                recognition.continuous = false;
                recognition.maxAlternatives = 1;

                let finalTranscript = '';
                const originalValue = lastField.value;

                recognition.onstart = () => {
                    listening = true;
                    btn.textContent = '🔴';
                    btn.style.background = '#dc2626';
                    btn.style.animation = 'voicePulse 1s infinite';
                    showStatus('🎙️ Słucham... mów teraz (' + (lang === 'pl-PL' ? 'po polsku' : 'in English') + ')');
                };

                recognition.onresult = (event) => {
                    let interim = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        const t = event.results[i][0].transcript;
                        if (event.results[i].isFinal) finalTranscript += t;
                        else interim += t;
                    }
                    const combined = (finalTranscript + interim).trim();
                    if (lastField.tagName === 'TEXTAREA') {
                        lastField.value = (originalValue ? originalValue + ' ' : '') + combined;
                    } else {
                        lastField.value = combined;
                    }
                    lastField.dispatchEvent(new Event('input', { bubbles: true }));
                    lastField.dispatchEvent(new Event('change', { bubbles: true }));
                };

                recognition.onerror = (event) => {
                    const msgs = {
                        'no-speech': 'Nie słyszę. Spróbuj jeszcze raz.',
                        'audio-capture': 'Brak dostępu do mikrofonu.',
                        'not-allowed': 'Mikrofon zablokowany. Brave: Shields ikona obok 🔒 → off. Chrome: kłódka 🔒 → mikrofon Allow.',
                        'network': 'Brak internetu (Web Speech wymaga połączenia z Google).',
                        'service-not-allowed': 'Brave blokuje Web Speech. Wyłącz Shields dla tej strony LUB użyj Chrome/Safari.',
                    };
                    console.error('Speech error:', event.error);
                    showStatus('⚠️ ' + (msgs[event.error] || event.error), '#dc2626');
                };

                recognition.onend = () => {
                    listening = false;
                    btn.textContent = '🎤';
                    btn.style.background = '#4f46e5';
                    btn.style.animation = '';
                    if (finalTranscript) showStatus('✓ Zapisano: "' + finalTranscript.slice(0, 50) + (finalTranscript.length > 50 ? '...' : '"'), '#16a34a');
                };

                recognition.start();
            });

            // CSS pulse animation
            const style = document.createElement('style');
            style.textContent = '@keyframes voicePulse { 0%,100% { box-shadow:0 4px 12px rgba(220,38,38,0.4);} 50% { box-shadow:0 4px 30px rgba(220,38,38,0.8);} }';
            document.head.appendChild(style);
        })();
        </script>
    </body>
</html>
