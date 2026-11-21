@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                    @if(\App\Helpers\ActiveRole::hasPermission('tambah data voucher'))
                        <a href="{{ route('vouchers.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i> Tambah Voucher
                        </a>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                            <h2>
                                Manajemen Voucher
                            </h2>
                            <div class="text-secondary small">Kode potongan harga untuk pendaftaran event.</div>
                            </div>
                        </div>
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        <div class="table-responsive">
                            <table id="tableVouchers" class="table card-table table-vcenter text-nowrap">
                                <thead>
                                    <tr>
                                        <th width="40">No</th>
                                        <th>Id Voucher</th>
                                        <th>Kode</th>
                                        <th>Nama Voucher</th>
                                        <th>Event</th>
                                        <th>Nilai</th>
                                        <th>Kuota (Terpakai)</th>
                                        <th>Masa Berlaku</th>
                                        <th>Status</th>
                                        <th width="90">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@if(\App\Helpers\ActiveRole::hasPermission('tambah data voucher'))
<a href="{{ route('vouchers.create') }}"
   class="mobile-fab d-md-none">

    <svg xmlns="http://www.w3.org/2000/svg"
         width="26"
         height="26"
         viewBox="0 0 24 24"
         stroke-width="2"
         stroke="currentColor"
         fill="none"
         stroke-linecap="round"
         stroke-linejoin="round">

        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <line x1="12" y1="5" x2="12" y2="19"/>
        <line x1="5" y1="12" x2="19" y2="12"/>

    </svg>

</a>
@endif
@endsection

@push('js')
<script>
$(function () {
    const table = $('#tableVouchers').DataTable({
        serverSide: true,
        processing: true,
        scrollX: true,
        ajax: '{{ route('vouchers.index') }}',
        order: [[1, 'desc']],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'voucher_number', name: 'voucher_number' },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'event_name', name: 'event_name', orderable: false },
            { data: 'value_label', orderable: false, searchable: false },
            { data: 'quota_label', orderable: false, searchable: false },
            { data: 'validity', orderable: false, searchable: false },
            { data: 'status_badge', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false },
        ],
        language: {
            search: '', searchPlaceholder: 'Cari voucher...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data', zeroRecords: 'Voucher tidak ditemukan',
            emptyTable: 'Belum ada voucher',
            paginate: { first: 'Awal', last: 'Akhir', next: '›', previous: '‹' }
        }
    });

    $('#tableVouchers').on('click', '.btn-delete-voucher', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Yakin hapus voucher ini?',
            text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/vouchers/${id}`,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    Swal.fire('Terhapus!', res.message, 'success');
                    table.ajax.reload(null, false);
                },
                error: function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus.', 'error');
                }
            });
        });
    });
});
</script>

@if (session('success'))
    <script>
        Swal.fire({ icon: 'success', title: 'Sukses!', text: @json(session('success')), timer: 2000, showConfirmButton: false });
    </script>
@endif
@endpush