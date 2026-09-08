@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('menus.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Data Menu</h2>
                
            </div>
        </div>
    </div>
</div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card shadow-sm border-0">
                <div class="card-body px-5 py-4">
                    <form action="{{ route('menus.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required">Judul Menu</label>
                                <input type="text" name="text" class="form-control" placeholder="Misal: Beranda" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tipe</label>
                                <select name="type" class="form-select select2">
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="route">Route</option>
                                    <option value="url">URL</option>
                                    <option value="label">Label</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="order" class="form-control" value="0">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">URL / Route Name</label>
                                <input type="text" name="url" class="form-control" placeholder="dashboard.index atau https://...">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Parent Menu</label>
                                <select name="parent_id" class="form-select select2">
                                    <option value="">-- Pilih Parent --</option>
                                    @foreach($parents as $menu)
                                        <option value="{{ $menu->id }}">{{ $menu->text }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Key</label>
                                <input type="text" name="key" class="form-control" value="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Icon (class)</label>
                                <input type="text" name="icon" class="form-control" placeholder="e.g., ti ti-home">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Aktif</label>
                                <select name="is_active" class="form-select select2">
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Permission yang Dibutuhkan (opsional)</label>
                                <select name="permission_name[]" class="form-select select2" multiple>
                                    <option value="">-- Tanpa batasan permission --</option>
                                    @foreach($permissions as $perm)
                                        <option value="{{ $perm->name }}">{{ $perm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i> Simpan Menu
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
  <script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });
    });
</script>  
@endpush