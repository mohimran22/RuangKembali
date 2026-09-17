@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('transactions.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Ubah Data Transaksi</h2>
                
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">

        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Edit Transaksi
                        </h3>
                        <div class="card-actions">
                            <a href="{{ route('transactions.show', $transaction->id) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-eye me-1"></i> Lihat Detail
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        {{-- Info ringkas, read-only --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label text-secondary">Event</label>
                                <div class="fw-bold">
                                    {{ $transaction->event->name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">Didaftarkan Oleh</label>
                                <div class="fw-bold">
                                    {{ $transaction->registeredBy->fullname
                                        ?? $transaction->registeredBy->name
                                        ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label text-secondary">Peserta</label>
                                <div class="fw-bold">
                                    @forelse ($transaction->registrations as $registration)
                                        {{ $registration->user->fullname ?? '-' }}{{ !$loop->last ? ', ' : '' }}
                                    @empty
                                        -
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4">

                        <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="status">
                                        Status Transaksi <span class="text-danger">*</span>
                                    </label>
                                    <select name="status"
                                            id="status"
                                            class="form-select @error('status') is-invalid @enderror">
                                        @php
                                            $statusOptions = [
                                                'pending' => 'Menunggu Pembayaran',
                                                'waiting_confirmation' => 'Menunggu Konfirmasi',
                                                'paid' => 'Lunas',
                                                'rejected' => 'Ditolak',
                                                'expired' => 'Kadaluarsa',
                                            ];
                                        @endphp
                                        @foreach ($statusOptions as $value => $label)
                                            <option value="{{ $value }}"
                                                @selected(old('status', $transaction->status) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="total_amount">
                                        Total Pembayaran (Rp) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           name="total_amount"
                                           id="total_amount"
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('total_amount') is-invalid @enderror"
                                           value="{{ old('total_amount', $transaction->total_amount) }}">
                                    @error('total_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-footer">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection