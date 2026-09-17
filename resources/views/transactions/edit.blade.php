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

                            {{-- Ganti kolom "Peserta" yang lama di info ringkas jadi cukup jumlah saja --}}
                            <div class="col-md-4">
                                <label class="form-label text-secondary">Jumlah Peserta</label>
                                <div class="fw-bold">
                                    {{ $transaction->registrations->count() }} orang
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
                                        Status Transaksi
                                    </label>
                                    <select id="status"
                                            class="form-select"
                                            disabled>
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
                                                @selected($transaction->status === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint">
                                        Status belum bisa diubah dari sini. Fitur perubahan status
                                        (termasuk minta upload ulang bukti transfer) sedang disiapkan.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Total Pembayaran (Rp)
                                    </label>
                                    <input type="text"
                                        class="form-control"
                                        value="Rp {{ number_format($transaction->registrations->sum('price'), 0, ',', '.') }}"
                                        disabled>
                                    <div class="form-hint">
                                        Total dihitung otomatis dari harga tiap peserta. Untuk mengoreksi
                                        total, ubah harga per peserta di bawah.
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">

                            <h4 class="mb-3">Daftar Peserta</h4>

                            @if ($transaction->registrations->isEmpty())
                                <p class="text-secondary">Belum ada peserta terdaftar.</p>
                            @else
                                @foreach ($transaction->registrations as $index => $registration)
                                    <div class="row mb-3 align-items-center">

                                        <input type="hidden"
                                            name="participants[{{ $index }}][id]"
                                            value="{{ $registration->id }}">

                                        <div class="col-md-4">
                                            <label class="form-label">
                                                Nama Peserta
                                                @if ($registration->user_id)
                                                    <span class="badge bg-blue-lt ms-1">Punya Akun</span>
                                                @else
                                                    <span class="badge bg-warning-lt ms-1">Belum Punya Akun</span>
                                                @endif
                                            </label>

                                            @if ($registration->user_id)
                                                <input type="text"
                                                    class="form-control"
                                                    value="{{ $registration->user->fullname ?? $registration->user->name ?? '-' }}"
                                                    disabled>
                                            @else
                                                <input type="text"
                                                    name="participants[{{ $index }}][guest_name]"
                                                    class="form-control @error("participants.$index.guest_name") is-invalid @enderror"
                                                    value="{{ old("participants.$index.guest_name", $registration->guest_name) }}">
                                                @error("participants.$index.guest_name")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Email Peserta</label>

                                            @if ($registration->user_id)
                                                <input type="text"
                                                    class="form-control"
                                                    value="{{ $registration->user->email ?? '-' }}"
                                                    disabled>
                                            @else
                                                <input type="email"
                                                    name="participants[{{ $index }}][guest_email]"
                                                    class="form-control @error("participants.$index.guest_email") is-invalid @enderror"
                                                    value="{{ old("participants.$index.guest_email", $registration->guest_email) }}">
                                                @error("participants.$index.guest_email")
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            @endif
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Tiket</label>
                                            <div class="fw-bold">{{ $registration->ticket_code ?? '-' }}</div>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Harga (Rp)</label>
                                            <input type="number"
                                                name="participants[{{ $index }}][price]"
                                                step="0.01"
                                                min="0"
                                                class="form-control @error("participants.$index.price") is-invalid @enderror"
                                                value="{{ old("participants.$index.price", $registration->price) }}">
                                            @error("participants.$index.price")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            @endif
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