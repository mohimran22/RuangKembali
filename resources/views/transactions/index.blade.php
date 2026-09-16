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
                </tbody>
            </table>
        </div>

    </div>
</div>

@endsection
@push('js')
<script>
$(function () {

    const isSuperAdmin = @json(
        auth()->user()->hasRole('Super-Admin')
    );

    let columns = [
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
        }
    ];
    if (isSuperAdmin) {
        columns.push({
            data: 'registered_by_name',
            name: 'registeredBy.fullname',
            orderable: false
        });
    }

    columns.push(
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
    );

    const table = $('#transactions-table').DataTable({

        processing: true,

        serverSide: true,

        ajax: {
            url: "{{ route('transactions.index') }}",
            type: "GET"
        },

        columns: columns,

        order: [
            [1, 'desc']
        ],

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
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
<script>
$(document).on('click', '.btn-delete-transaction', function () {
    const transactionId = $(this).data('id');

    Swal.fire({
        title: 'Yakin hapus transaksi ini?',
        text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/transactions/${transactionId}`,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    Swal.fire('Terhapus!', res.message, 'success');
                    $('#transactions-table').DataTable().ajax.reload(null, false);
                },
                error: function () {
                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus transaksi.', 'error');
                }
            });
        }
    });
});
</script>
@endpush