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
            <table
                id="transactions-table"
                class="table card-table table-vcenter"
                style="width: 100%;"
            >
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Event</th>
                        <th>Didaftarkan Oleh</th>
                        <th>Jumlah Peserta</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>

    </div>

</div>
<script>
$(function () {

    $('#transactions-table').DataTable({

        processing: true,

        serverSide: true,

        ajax: {
            url: "{{ route('admin.transactions.index') }}",
            type: "GET",

            data: function (d) {
                d.status = "{{ $status }}";
            }
        },

        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'transaction_code',
                name: 'transaction_code'
            },
            {
                data: 'event_name',
                name: 'event.name',
                orderable: false
            },
            {
                data: 'registered_by_name',
                name: 'registeredBy.fullname',
                orderable: false
            },
            {
                data: 'participants',
                name: 'registrations_count',
                orderable: false,
                searchable: false
            },
            {
                data: 'total_amount',
                name: 'total_amount'
            },
            {
                data: 'status_badge',
                name: 'status',
                orderable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],

        order: [
            [1, 'desc']
        ],

        pageLength: 15,

        lengthMenu: [
            [15, 25, 50, 100],
            [15, 25, 50, 100]
        ],

        language: {
            processing: 'Memuat data...',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi',
            infoEmpty: 'Tidak ada transaksi',
            zeroRecords: 'Transaksi tidak ditemukan',
            emptyTable: 'Belum ada transaksi',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: '›',
                previous: '‹'
            }
        },

        drawCallback: function () {

            document
                .querySelectorAll('[data-bs-toggle="tooltip"]')
                .forEach(function (element) {

                    new bootstrap.Tooltip(element);

                });

        }

    });

});
</script>
@endsection