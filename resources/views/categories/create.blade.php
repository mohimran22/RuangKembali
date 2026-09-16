@extends('tablar::page')

@section('content')
    <!-- Page header -->
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('event_categories.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Data Kategori</h2>
                
            </div>
        </div>
    </div>
</div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <div class="card shadow-sm border-0">
                <div class="card-body px-5 py-4">
                    <form action="{{ route('event_categories.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Nama Kategori --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">
                                    Nama Kategori <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    required
                                >

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Slug --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Slug</label>

                                <input
                                    type="text"
                                    name="slug"
                                    class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug') }}"
                                    placeholder="contoh: seminar-nasional"
                                >

                                @error('slug')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <small class="text-muted">
                                    Kosongkan jika slug ingin dibuat otomatis.
                                </small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kategori Aktif</label>
                                <select
    name="is_active"
    class="form-select @error('is_active') is-invalid @enderror"
>
    <option value="">-- Pilih Status --</option>
    <option value="1" {{ old('is_active') === '1' ? 'selected' : '' }}>
        Aktif
    </option>
    <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>
        Tidak Aktif
    </option>
</select>

@error('is_active')
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror
                            </div>

                            {{-- Deskripsi --}}
                            <div class="col-12 mb-3">
                                <label class="form-label">Deskripsi</label>

                                <textarea
                                    name="description"
                                    rows="4"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Deskripsi kategori..."
                                >{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>    
        </div>
    </div>
@endsection
