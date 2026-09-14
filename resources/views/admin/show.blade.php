@extends('tablar::page')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">
        <h2 class="page-title">Detail Transaksi</h2>
        <div class="text-secondary">{{ $transaction->transaction_code }}</div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="ti ti-circle-check me-2"></i>
            {{ session('success') }}
        </div>
    @endif


    <div class="row g-4">

        {{-- Ringkasan --}}
        <div class="col-lg-5">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <div class="text-secondary small">Event</div>
                        <div class="fw-semibold">{{ $transaction->event->name }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">Pendaftar</div>
                        <div class="fw-semibold">
                            {{ $transaction->registeredBy->fullname ?? $transaction->registeredBy->name }}
                        </div>
                        <div class="text-secondary small">{{ $transaction->registeredBy->email }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-secondary small">Metode Pembayaran</div>
                        <div class="fw-semibold text-capitalize">{{ $transaction->payment_method }}</div>
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
                                        {{ $registration->user->fullname ?? $registration->user->name }}
                                    </div>
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


        {{-- Bukti Transfer & Aksi --}}
        <div class="col-lg-7">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Verifikasi Pembayaran</h3>
                </div>

                <div class="card-body">

                    @if($transaction->status === 'paid')

                        <div class="alert alert-success">
                            <div class="d-flex">
                                <i class="ti ti-circle-check fs-2 me-3"></i>
                                <div>
                                    <h3 class="alert-title">Sudah Disetujui</h3>
                                    <div class="text-secondary">
                                        Dikonfirmasi pada {{ $transaction->paid_at->translatedFormat('d F Y, H:i') }} WIB.
                                    </div>
                                </div>
                            </div>
                        </div>

                    @elseif($transaction->status === 'rejected')

                        <div class="alert alert-danger">
                            <div class="d-flex">
                                <i class="ti ti-alert-circle fs-2 me-3"></i>
                                <div>
                                    <h3 class="alert-title">Ditolak</h3>
                                    @if($transaction->rejection_reason)
                                        <div class="text-secondary">Alasan: {{ $transaction->rejection_reason }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @elseif($transaction->status === 'pending')

                        <div class="alert alert-warning">
                            <i class="ti ti-clock me-2"></i>
                            User belum mengupload bukti transfer.
                        </div>

                    @endif


                    @if($transaction->proof_of_payment)
                        <div class="mb-4">
                            <div class="text-secondary small mb-2">Bukti Transfer</div>
                            <a href="{{ Storage::url($transaction->proof_of_payment) }}" target="_blank">
                                <img src="{{ Storage::url($transaction->proof_of_payment) }}"
                                     class="img-fluid rounded border"
                                     style="max-height: 400px;">
                            </a>
                        </div>
                    @endif


                    @if($transaction->status === 'waiting_confirmation')

                        <div class="row g-2">

                            <div class="col-6">
                                <form action="{{ route('admin.transactions.approve', $transaction->id) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="ti ti-check me-1"></i>
                                        Setujui (Lunas)
                                    </button>
                                </form>
                            </div>

                            <div class="col-6">
                                <button type="button"
                                        class="btn btn-danger w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectModal">
                                    <i class="ti ti-x me-1"></i>
                                    Tolak
                                </button>
                            </div>

                        </div>


                        {{-- Modal alasan penolakan --}}
                        <div class="modal fade" id="rejectModal" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <form action="{{ route('admin.transactions.reject', $transaction->id) }}"
                                          method="POST">
                                        @csrf

                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak Bukti Transfer</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <label class="form-label">Alasan Penolakan (opsional)</label>
                                            <textarea name="rejection_reason"
                                                      class="form-control"
                                                      rows="3"
                                                      placeholder="Contoh: Nominal transfer tidak sesuai"></textarea>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">
                                                Batal
                                            </button>
                                            <button type="submit" class="btn btn-danger">
                                                Tolak Transaksi
                                            </button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection