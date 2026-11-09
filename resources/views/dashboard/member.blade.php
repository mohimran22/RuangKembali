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
                                        <td><code>{{ $reg->ticket_code }}</code></td>
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
@endsection