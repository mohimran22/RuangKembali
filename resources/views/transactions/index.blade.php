@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none mb-4">
        <h2 class="page-title">Transaksi</h2>
        <div class="text-secondary" style="margin-left:20px;">
            @if($isStaff)
                Daftar seluruh transaksi pendaftaran event di sistem.
            @else
                Daftar transaksi pendaftaran event kamu.
            @endif
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            @if($isStaff)
                <div class="mb-3">
                    <label class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="filterGuest">
                        <span class="form-check-label">Hanya transaksi yang berisi peserta tamu</span>
                    </label>
                </div>
            @endif
            <div class="row row-deck row-cards">
                <div class="col-12">
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

                                        @if($isStaff)
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
            </div>
        </div>
    </div>


@endsection
@push('js')
<script>
$(function () {

    const isStaff = @json($isStaff);

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
    if (isStaff) {
        columns.push({
            data: 'registered_by_name',
            name: 'registeredBy.fullname',
            orderable: false
        });
    }

    columns.push(
        {
            data: 'participants',
            name: 'participants',
            orderable: false,
            searchable: true
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
            type: "GET",
            data: function (d) {
                d.guest = $('#filterGuest').is(':checked') ? 1 : 0;
            }
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
            search: "",
            searchPlaceholder: "Cari transaksi...",
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
    $('#filterGuest').on('change', function () {
        table.ajax.reload();
    });
});
</script>
<script>
$(document).on('click', '.btn-delete-transaction', function () {
    const transactionId = $(this).data('id');
    const isPaid = Number($(this).data('paid')) === 1;

    const config = isPaid
        ? {
            title: 'Hapus transaksi LUNAS?',
            html: 'Tindakan ini menghapus <b>jurnal akuntansi</b> yang terhubung dan <b>semua peserta</b> pada transaksi ini. Tidak bisa dikembalikan.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Alasan penghapusan (wajib)',
            inputAttributes: { 'aria-label': 'Alasan penghapusan' },
            inputValidator: (value) =>
                (!value || value.trim().length < 5) ? 'Alasan minimal 5 karakter.' : null,
            confirmButtonText: 'Ya, hapus semuanya',
        }
        : {
            title: 'Yakin hapus transaksi ini?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            confirmButtonText: 'Ya, hapus',
        };

    Swal.fire({
        ...config,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonText: 'Batal',
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            url: `/admin/transactions/${transactionId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                reason: isPaid ? result.value : null,
            },
            success: function (res) {
                Swal.fire('Terhapus!', res.message, 'success');
                $('#transactions-table').DataTable().ajax.reload(null, false);
            },
            error: function (xhr) {
                Swal.fire(
                    'Gagal',
                    xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus transaksi.',
                    'error'
                );
            }
        });
    });
});
</script>
@endpush