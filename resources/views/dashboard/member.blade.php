@extends('tablar::page')

@section('content')
@php
    // Status ditentukan dari pendaftaran DAN transaksinya
    $regStatus = function ($reg) {
        $trx = $reg->transaction?->status;

        return match (true) {
            $reg->status === 'attended' => ['info', 'Sudah Hadir', null],
            $reg->status === 'paid' => ['success', 'Terdaftar', null],
            $reg->status === 'pending' && $trx === 'waiting_confirmation' => ['blue', 'Menunggu Konfirmasi', 'Lihat'],
            $reg->status === 'pending' && $trx === 'rejected' => ['danger', 'Bukti Ditolak', 'Upload Ulang'],
            $reg->status === 'pending' => ['warning', 'Menunggu Pembayaran', 'Bayar'],
            $reg->status === 'cancelled' => ['danger', 'Dibatalkan', null],
            default => ['secondary', ucfirst($reg->status), null],
        };
    };
@endphp

<div class="page-body">
    <div class="container-xl pt-4">

        <h2 class="text-center mb-4 pt-5">
            Selamat Datang {{ auth()->user()->fullname }} di Sistem Ruang Kembali
        </h2>

        <div class="row g-4">

            {{-- ============ EVENT BERLANGSUNG ============ --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header"><h5 class="mb-0">🔴 Event Sedang Berlangsung</h5></div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @forelse($ongoingEvents as $event)
                                <div class="col-md-6 col-xl-4">
                                    <div class="border rounded-3 p-3 h-100">
                                        <div class="fw-bold">{{ $event->name }}</div>
                                        <div class="text-secondary small mt-1">
                                            {{ $event->start_at->translatedFormat('d M Y, H:i') }}
                                            @if($event->location) · {{ $event->location }} @endif
                                        </div>
                                        <div class="mt-2">
                                            👥 {{ $event->quota ? 'Kuota '.$event->quota.' peserta' : 'Kuota tidak terbatas' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-secondary">Tidak ada event yang sedang berlangsung.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ EVENT SAYA ============ --}}
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header"><h5 class="mb-0">🎟 Event yang Saya Daftar</h5></div>
                    <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-sm">
                            <thead>
                                <tr>
                                    <th>Nama Event</th>
                                    <th>Peserta</th>
                                    <th>Tanggal</th>
                                    <th>Kode Tiket</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($myRegistrations as $reg)
                                    @php [$color, $label, $action] = $regStatus($reg); @endphp
                                    <tr>
                                        <td>{{ $reg->event?->name ?? '-' }}</td>
                                        <td>{{ $reg->participant_name ?? '-' }}</td>
                                        <td>{{ $reg->event?->start_at?->translatedFormat('d M Y') ?? '-' }}</td>
                                        <td>
    <div class="d-flex align-items-center gap-2">
        <code>{{ $reg->ticket_code }}</code>

        @if(in_array($reg->status, ['paid', 'attended']))
            <button type="button"
                    class="btn btn-icon btn-sm btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#ticketQrModal"
                    data-ticket-code="{{ $reg->ticket_code }}"
                    data-name="{{ $reg->participant_name }}"
                    data-event="{{ $reg->event?->name }}"
                    title="Tampilkan QR Code">
                <i class="ti ti-qrcode"></i>
            </button>
        @endif
    </div>
</td>
                                        <td><span class="badge bg-{{ $color }}-lt">{{ $label }}</span></td>
                                        <td class="text-end">
                                            @if($action && $reg->transaction_id)
                                                <a href="{{ route('transactions.show', $reg->transaction_id) }}"
                                                class="btn btn-sm btn-outline-{{ $color === 'blue' ? 'primary' : $color }}">
                                                    {{ $action }}
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-secondary">Kamu belum mendaftar event apa pun.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal modal-blur fade" id="ticketQrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ti ti-qrcode me-2"></i>
                    QR Code Tiket
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="fw-semibold" id="ticketQrEvent">-</div>
                <div class="text-secondary small" id="ticketQrName">-</div>
                <div class="text-secondary small mb-3"><code id="ticketQrCode">-</code></div>

                <div class="d-inline-block border rounded p-3 bg-white">
                    <div id="ticketQrBox"></div>
                </div>

                <div class="text-secondary small mt-3">
                    Tunjukkan QR ini ke panitia saat check-in.
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('ticketQrModal');
        if (!modalEl) return;

        const box = document.getElementById('ticketQrBox');

        modalEl.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            const code = btn.dataset.ticketCode;

            document.getElementById('ticketQrEvent').textContent = btn.dataset.event || '';
            document.getElementById('ticketQrName').textContent = btn.dataset.name || '';
            document.getElementById('ticketQrCode').textContent = code;

            box.innerHTML = '';
            new QRCode(box, {
                text: code,
                width: 220,
                height: 220,
                correctLevel: QRCode.CorrectLevel.M,
            });
        });
    });
</script>
@endpush
@endsection