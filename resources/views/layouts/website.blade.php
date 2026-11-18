<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @guest
        @if ($aaPageViewId = request()->attributes->get('analytics_page_view_id'))
            <meta name="aa-page-view-id" content="{{ $aaPageViewId }}">
        @endif
    @endguest

    <meta name="csrf-token" content="{{ csrf_token() }}">


    {!! SEO::generate() !!}

    <!-- sesuaikan dengan brand guidelines -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Saira:wght@600;700&display=swap" rel="stylesheet">
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="{{ asset('fonts/tabler-icons.css') }}">
   @include('partials.footer-css')
    <!-- CSS tambahan -->
    @stack('styles')
</head>

<body>

    @include('partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const pageViewId = document.querySelector(
            'meta[name="aa-page-view-id"]'
        )?.content;

        if (!pageViewId) return;

        const startedAt = Date.now();

        let activeSeconds = 0;
        let maxScrollPercent = 0;
        let interactionCount = 0;

        let lastActiveAt = Date.now();
        let lastSentAt = 0;
        let lastSnapshot = '0|0|0';   // tambahkan baris ini

        const csrfToken = document.querySelector(
            'meta[name="csrf-token"]'
        )?.content;

        if (!csrfToken) return;

        function isActive() {
            return document.visibilityState === 'visible' &&
                document.hasFocus();
        }

        function recordActivity() {
            if (!isActive()) return;

            lastActiveAt = Date.now();
        }

        // Hitung waktu aktif, bukan sekadar waktu tab terbuka.
        setInterval(function () {
            const now = Date.now();

            if (isActive() && now - lastActiveAt <= 30000) {
                activeSeconds++;
            }
        }, 1000);

        // Deteksi interaksi pengunjung.
        ['click', 'keydown', 'touchstart'].forEach(function (eventName) {
            document.addEventListener(eventName, function () {
                if (!isActive()) return;

                interactionCount++;
                recordActivity();
            }, { passive: true });
        });

        // Deteksi scroll maksimum.
        window.addEventListener('scroll', function () {
            const documentHeight =
                document.documentElement.scrollHeight - window.innerHeight;

            if (documentHeight <= 0) return;

            const percent = Math.round(
                (window.scrollY / documentHeight) * 100
            );

            maxScrollPercent = Math.max(
                maxScrollPercent,
                Math.min(100, percent)
            );

            recordActivity();
        }, { passive: true });

        window.addEventListener('mousemove', recordActivity, {
            passive: true
        });

        window.addEventListener('pointerdown', recordActivity, {
            passive: true
        });

        function sendAnalytics() {
            const now = Date.now();

            // Hindari pengiriman berulang dalam waktu sangat singkat.
            if (now - lastSentAt < 1000) return;
            const snapshot = activeSeconds + '|' + maxScrollPercent + '|' + interactionCount;
            if (snapshot === lastSnapshot) return;
            lastSentAt = now;
            lastSnapshot = snapshot;

            const payload = {
                _token: csrfToken,
                page_view_id: pageViewId,
                duration_seconds: Math.min(86400, Math.floor((now - startedAt) / 1000)),
                active_seconds: activeSeconds,
                max_scroll_percent: maxScrollPercent,
                interaction_count: interactionCount
            };

            const blob = new Blob(
                [JSON.stringify(payload)],
                { type: 'application/json' }
            );

            if (navigator.sendBeacon) {
                navigator.sendBeacon(
                    @json(route('analytics.track')),
                    blob
                );
            } else {
                fetch(@json(route('analytics.track')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    keepalive: true,
                    body: JSON.stringify(payload)
                });
            }
        }

        // Kirim data saat halaman disembunyikan.
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'hidden') {
                sendAnalytics();
            } else {
                lastActiveAt = Date.now();
            }
        });

        // Kirim data saat pengunjung meninggalkan halaman.
        window.addEventListener('pagehide', sendAnalytics);

        // Backup: kirim data secara berkala.
        setInterval(sendAnalytics, 15000);
    });
    </script>

    @stack('scripts')
    @yield('scripts')

</body>

</html>