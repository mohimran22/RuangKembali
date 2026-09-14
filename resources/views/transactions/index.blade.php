@extends('tablar::page')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">
        <h2 class="page-title">Transaksi</h2>
        <div class="text-secondary" style="margin-left:20px;">
            @if(auth()->user()->hasRole('Super-Admin'))
                Daftar seluruh transaksi pendaftaran event di sistem.
            @else
                Daftar transaksi pendaftaran event kamu.
            @endif
        </div>
    </div>

    <div class="card">

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Event</th>
                        @if(auth()->user()->hasRole('Super-Admin'))
                            <th>Didaftarkan Oleh</th>
                        @endif
                        <th>Jumlah Peserta</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>{{ $transaction->event->name }}</td>
                            @if(auth()->user()->hasRole('Super-Admin'))
                                <td>{{ $transaction->registeredBy->fullname ?? $transaction->registeredBy->name ?? '-' }}</td>
                            @endif
                            <td>
                                {{ $transaction->registrations_count }}
                                @if($transaction->registrations_count > 0)
                                    <i class="ti ti-info-circle text-secondary ms-1"
                                    data-bs-toggle="tooltip"
                                    title="{{ $transaction->registrations->pluck('user.fullname')->implode(', ') }}"></i>
                                @endif
                            </td>
                            <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            <td>
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
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">

                                    <a href="{{ auth()->user()->hasAnyRole(['Super-Admin', 'Tim'])
                                            ? route('admin.transactions.show', $transaction->id)
                                            : route('transactions.show', $transaction->id) }}"
                                    class="btn btn-sm btn-primary"
                                    title="Lihat Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>

                                    {{-- Tombol cepat ke halaman approve/reject: cuma admin/team, cuma kalau sudah upload bukti --}}
                                    @if(auth()->user()->hasAnyRole(['Super-Admin', 'Tim']) && $transaction->status === 'waiting_confirmation')
                                        <a href="{{ route('admin.transactions.show', $transaction->id) }}"
                                        class="btn btn-sm btn-outline-warning"
                                        title="Perlu Verifikasi">
                                            <i class="ti ti-clock-check"></i>
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                Belum ada transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="card-footer">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

</div>

@endsection