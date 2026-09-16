@extends('tablar::page')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-auto ms-auto d-print-none">
                <div class="btn-list">
                {{-- @can('tambah data karyawan')        --}}
                <span class="d-none d-sm-inline">
                    <a href="{{ route("event_categories.create") }}" class="btn btn-primary d-none d-sm-inline-block" >
                        <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tambah Data Kategori
                    </a>
                </span>
                {{-- @endcan --}}
                    
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
                        <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Montserrat', sans-serif;">
                                Daftar Kategori Event
                        </p>
                    </div>
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                        <div class="table-responsive">
                            <table class="table card-table table-vcenter">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Nama Kategori</th>
                                        <th>Slug</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($eventcategory as $cat)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            
                                            <td>{{ $cat->name }}</td>
                                            <td>{{ $cat->slug }}</td>
                                            <td>{{ $cat->description }}</td>
                                            <td>
                                                <a href="{{ route('event_categories.edit', $cat) }}"
                                                class="btn btn-sm btn-warning"
                                                title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
<form
    action="{{ route('event_categories.destroy', $cat) }}"
    method="POST"
    class="d-inline-block delete-category-form"
    data-name="{{ $cat->name }}"
>
    @csrf
    @method('DELETE')

    <button
        type="submit"
        class="btn btn-sm btn-danger"
        title="Hapus"
    >
        <i class="ti ti-trash"></i>
    </button>
</form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                </div>
            </div>    
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-category-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const categoryName = form.dataset.name;

            Swal.fire({
                title: 'Hapus Kategori?',
                html: `Kategori <strong>${categoryName}</strong> akan dihapus.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
