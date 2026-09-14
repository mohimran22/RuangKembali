@extends('tablar::page')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">
        <h2 class="page-title">Kelola Transaksi</h2>
        <div class="text-secondary">Verifikasi pembayaran pendaftaran event.</div>
    </div>

    <div class="card">

        <div class="card-header">
            <div class="btn-group">
                <a href="{{ route('admin.transactions.index') }}"
                   class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua
                </a>
                <a href="{{ route('admin.transactions.index', ['status' => 'waiting_confirmation']) }}"
                   class="btn btn-sm {{ $status === 'waiting_confirmation' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Menunggu Konfirmasi
                </a>
                <a href="{{ route('admin.transactions.index', ['status' => 'paid']) }}"
                   class="btn btn-sm {{ $status === 'paid' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Lunas
                </a>
                <a href="{{ route('admin.transactions.index', ['status' => 'rejected']) }}"
                   class="btn btn-sm {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Ditolak
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Event</th>
                        <th>Pendaftar</th>
                        <th>Peserta</th>
                        <th>Total Harga</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>{{ $transaction->event->name }}</td>
                            <td>{{ $transaction->registeredBy->fullname ?? $transaction->registeredBy->name }}</td>
                            <td>{{ $transaction->registrations_count }}</td>
                            <td>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                            <td class="text-capitalize">{{ $transaction->payment_method }}</td>
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
                                <a href="{{ route('admin.transactions.show', $transaction->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-secondary py-4">
                                Tidak ada transaksi.
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