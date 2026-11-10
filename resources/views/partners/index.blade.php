@extends('tablar::page')

@section('content')
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-auto ms-auto d-print-none">
                    <div class="btn-list">
                        @can('tambah data mitra')       
                            <a href="{{ route("partners.create") }}" class="btn btn-primary" >
                                <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Tambah Data Mitra
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-deck row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                 Daftar Mitra
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table id="tablePartners" class="table card-table table-vcenter text-nowrap" >
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>ID Mitra</th>
                                        <th>Nama Pengguna</th>
                                        <th>Email Pengguna</th>
                                        <th>Nama Mitra</th>
                                        <th>Telpon Kantor</th>
                                        <th>Alamat Kantor</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@can('tambah data mitra')
<a href="{{ route('partners.create') }}"
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
@endcan
@endsection

@push('js')
    <script>
        $(function() {
            const isMobile = window.innerWidth < 576;
            const table = $('#tablePartners').DataTable({
                scrollY: '500px',
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: !isMobile ? {
                    leftColumns: 4
                } : false,
                serverSide: true,
                processing: true,
                responsive: false,
                ajax: '{{ route("partners.index") }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'architect_id', name: 'architect_id' },
                    { data: 'fullname', name: 'fullname' },
                    { data: 'email', name: 'email' },
                    { data: 'architect_name', name: 'architect_name' },
                    { data: 'architect_phone', name: 'architect_phone' },
                    { data: 'architect_address', name: 'architect_address' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },  
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Cari mitra...",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "›",
                        previous: "‹"
                    }
                },

                initComplete: function () {
                    const input = $('.dt-search input');
                    input.removeClass('form-control-sm')
                        .addClass('form-control');
                }
            });

            // Delete user functionally
            $('table').on('click', '.delete-architect', function () {
            const architectId = $(this).data('id');

            Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data akan hilang secara permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'

            }).then((result) => {

                if (result.isConfirmed) {
                    $.ajax({

                        url: `/partners/${architectId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },

                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: 'Pemilik telah dihapus.',
                                    timer: 2000,
                                    showConfirmButton: false
                            });

                        table.ajax.reload(null, false); // refresh datatable
                        } else {

                            Swal.fire('Gagal', response.message || 'Tidak bisa menghapus data.', 'error');
                        }
                        },

                    error: function () {

                    Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                    }

                    });
                }
            });
            });


           
        });
    </script>
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
@endpush