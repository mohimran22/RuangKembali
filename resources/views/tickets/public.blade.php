<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Tiket - {{ $event->name }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0/dist/css/tabler.min.css">
</head>
<body class="bg-light">
@php
    $status = $registration->status;
    $active = in_array($status, ['paid', 'confirmed', 'attended']);
@endphp

<div class="container py-4" style="max-width: 420px;">
    <div class="card shadow-sm">
        <div class="card-body text-center">

            <div class="text-secondary small mb-1">{{ $event->category?->name ?? 'Tiket Event' }}</div>
            <h2 class="mb-3">{{ $event->name }}</h2>

            <div class="fw-semibold">{{ $registration->participant_name }}</div>

            <div class="text-secondary small mt-3">
                {{ $event->start_at->translatedFormat('l, d F Y') }}<br>
                {{ $event->start_at->format('H:i') }} - {{ $event->end_at->format('H:i') }} WIB
            </div>

            @if($event->location)
                <div class="text-secondary small mt-2">
                    {{ $event->location }}
                    @if($event->google_maps_url)
                        <br><a href="{{ $event->google_maps_url }}" target="_blank" rel="noopener noreferrer">Buka di Google Maps</a>
                    @endif
                </div>
            @endif

            <hr>

            @if($active)
                @if($status === 'attended')
                    <div class="alert alert-info">Tiket ini sudah digunakan untuk check-in.</div>
                @endif

                <div class="d-inline-block border rounded p-3 bg-white">
                    <div id="qrBox"></div>
                </div>

                <div class="mt-3 fs-2 fw-bold">{{ $registration->ticket_code }}</div>
                <div class="text-secondary small mt-2">
                    Tunjukkan QR ini ke panitia saat check-in.
                    Jika QR tidak terbaca, sebutkan kode tiket di atas.
                </div>
            @elseif($status === 'cancelled')
                <div class="alert alert-danger mb-0">Pendaftaran ini sudah dibatalkan. Tiket tidak berlaku.</div>
            @else
                <div class="alert alert-warning mb-0">Tiket belum aktif. Pembayaran belum dikonfirmasi.</div>
            @endif

        </div>
    </div>
</div>

@if($active)
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qrBox'), {
        text: @json($registration->ticket_code),
        width: 240,
        height: 240,
        correctLevel: QRCode.CorrectLevel.M,
    });
</script>
@endif
</body>
</html>