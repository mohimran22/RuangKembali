@extends('tablar::page')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2>
                    Detail Transaksi
                </h2>
                <div class="text-secondary">
                    {{ $transaction->transaction_code }}
                </div>
            </div>
        </div>
    </div>


    @if(session('success'))
        <div class="alert alert-success">
            <i class="ti ti-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>
    @endif


    <div class="row g-4">

        {{-- Ringkasan Transaksi --}}
        <div class="col-lg-5">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-receipt me-2"></i>
                        Ringkasan
                    </h3>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <div class="text-secondary small">Event</div>
                        <div class="fw-semibold">{{ $transaction->event->name }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">No. Transaksi</div>
                        <div class="fw-semibold">{{ $transaction->transaction_code }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">Status</div>
                        <div>
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-warning-lt'],
                                    'waiting_confirmation' => ['label' => 'Menunggu Konfirmasi', 'class' => 'bg-blue-lt'],
                                    'paid' => ['label' => 'Lunas', 'class' => 'bg-success-lt'],
                                    'rejected' => ['label' => 'Ditolak', 'class' => 'bg-danger-lt'],
                                    'expired' => ['label' => 'Kadaluarsa', 'class' => 'bg-secondary-lt'],
                                ];
                                $currentStatus = $statusMap[$transaction->status] ?? ['label' => $transaction->status, 'class' => 'bg-secondary-lt'];
                            @endphp

                            <span class="badge {{ $currentStatus['class'] }}">
                                {{ $currentStatus['label'] }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="text-secondary small mb-2">
                            Daftar Peserta ({{ $transaction->registrations->count() }})
                        </div>

                        @foreach($transaction->registrations as $registration)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $registration->participant_name }}
                                        @if(!$registration->user_id)
                                            <span class="badge bg-orange-lt ms-1">Belum punya akun</span>
                                        @endif
                                    </div>
                                    <div class="text-secondary small">{{ $registration->participant_email }}</div>
                                    <div class="text-secondary small">{{ $registration->ticket_code }}</div>
                                </div>
                                <div class="text-secondary small">
                                    Rp {{ number_format($registration->price, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <div class="fw-bold">Total Harga</div>
                        <div class="fs-3 fw-bold text-primary">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </div>
                    </div>

                </div>
            </div>

        </div>


        {{-- Aksi Pembayaran --}}
        <div class="col-lg-7">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-credit-card me-2"></i>
                        Pembayaran
                    </h3>
                </div>

                <div class="card-body">

                    @if($transaction->status === 'paid')

                        <div class="alert alert-success">
                            <div class="d-flex">
                                <i class="ti ti-circle-check fs-2 me-3"></i>
                                <div>
                                    <h3 class="alert-title">Pembayaran Berhasil</h3>
                                    <div class="text-secondary">Transaksi ini sudah lunas.</div>
                                </div>
                            </div>
                        </div>

                    @elseif($transaction->status === 'waiting_confirmation')

                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="ti ti-clock fs-2 me-3"></i>
                                <div>
                                    <h3 class="alert-title">Menunggu Konfirmasi</h3>
                                    <div class="text-secondary">Bukti transfer sudah diupload, mohon tunggu verifikasi dari admin.</div>
                                </div>
                            </div>
                        </div>

                        @if($transaction->proof_of_payment)
                            <div class="mt-3">
                                <div class="text-secondary small mb-2">Bukti Transfer yang Diupload</div>
                                <img src="{{ Storage::url($transaction->proof_of_payment) }}"
                                     class="img-fluid rounded border"
                                     style="max-height: 300px;">
                            </div>
                        @endif

                        @elseif($transaction->status === 'expired')

                            <div class="alert alert-secondary">
                                <div class="d-flex">
                                    <i class="ti ti-clock-off fs-2 me-3"></i>
                                    <div>
                                        <h3 class="alert-title">Transaksi Kadaluarsa</h3>
                                        <div class="text-secondary">Batas waktu pembayaran sudah lewat. Silakan daftar ulang ke event ini.</div>
                                    </div>
                                </div>
                            </div>

                        @elseif($transaction->status === 'rejected')

                        <div class="alert alert-danger mb-3">
                            <div class="d-flex">
                                <i class="ti ti-alert-circle fs-2 me-3"></i>
                                <div>
                                    <h3 class="alert-title">Bukti Transfer Ditolak</h3>
                                    <div class="text-secondary">Silakan upload ulang bukti transfer yang valid.</div>
                                </div>
                            </div>
                        </div>

                        @include('transactions.upload-form')

                        @elseif($transaction->payment_method === 'transfer')

                            @php $event = $transaction->event; @endphp

                            @if($event->transfer_account_number)

                                <div class="alert alert-info mb-3">
                                    <div class="d-flex">
                                        <i class="ti ti-info-circle fs-2 me-3"></i>
                                        <div>Silakan transfer ke rekening berikut, lalu upload bukti transfer.</div>
                                    </div>
                                </div>

                                @if($transaction->expires_at)
                                    <div class="alert alert-warning mb-3">
                                        <div class="d-flex">
                                            <i class="ti ti-clock fs-2 me-3"></i>
                                            <div>
                                                Selesaikan pembayaran dan upload bukti sebelum
                                                <strong>{{ $transaction->expires_at->translatedFormat('d F Y, H:i') }} WIB</strong>,
                                                atau pendaftaran akan otomatis dibatalkan.
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="card card-sm border mb-3">
                                    <div class="card-body">
                                        <div class="text-secondary small">{{ $event->transfer_bank_name }}</div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="fw-semibold fs-3">{{ $event->transfer_account_number }}</div>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    data-copy="{{ $event->transfer_account_number }}"
                                                    onclick="navigator.clipboard.writeText(this.dataset.copy); this.textContent = 'Tersalin';">
                                                Salin
                                            </button>
                                        </div>
                                        <div class="text-secondary">a.n. {{ $event->transfer_account_holder }}</div>
                                        <div class="text-secondary small mt-2">
                                            Nominal: <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>

                                @include('transactions.upload-form')

                            @else

                                <div class="alert alert-warning">
                                    <i class="ti ti-alert-triangle me-2"></i>
                                    Informasi rekening untuk event ini belum diatur panitia. Silakan hubungi panitia event sebelum melakukan pembayaran.
                                </div>

                            @endif

                        @elseif($transaction->payment_method === 'gateway')

                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                Kamu memilih Payment Gateway. Klik tombol di bawah untuk melanjutkan pembayaran.
                            </div>

                            {{-- TODO: ganti dengan redirect ke Midtrans/Xendit snap token/link --}}
                            <button class="btn btn-primary w-100" disabled>
                                <i class="ti ti-credit-card me-1"></i>
                                Bayar Sekarang (integrasi belum aktif)
                            </button>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection