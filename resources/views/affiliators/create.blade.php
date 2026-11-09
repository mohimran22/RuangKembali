@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('affiliators.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Affiliator</h2>
                
            </div>
        </div>
    </div>
</div>


<div class="page-body">
    <div class="container-xl">
        <div class="card shadow-sm border-0">
            <div class="card-body px-5 py-4">
                <form action="{{ route('affiliators.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                    <div class="text-center mb-5">
                    <div class="position-relative d-inline-block">
                        @if ($user->photo)
                            <img id="previewImage" src="{{ asset('storage/photos/'.$user->photo) }}" alt="Profile" 
                                 class="rounded-3 shadow-sm border" width="150" height="150"
                                 style="object-fit: cover;">
                        @else
                            <div id="previewImage"
                                 class="rounded-3 shadow-sm bg-light d-flex align-items-center justify-content-center"
                                 style="width:150px; height:150px;">
                                 <i class="ti ti-user" style="font-size: 64px; color:#aaa;"></i>
                            </div>
                        @endif
                        <label for="photo"
                               class="btn btn-sm btn-primary position-absolute bottom-0 end-0 translate-middle rounded-circle"
                               title="Ganti Foto">
                            <i class="ti ti-camera"></i>
                        </label>
                    </div>
                    <input type="file" id="photo" name="photo" class="d-none" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <small class="text-danger fw-semibold">
                            * : Wajib diisi
                        </small>
                    </div>

                    {{-- ========== SECTION 1: INFORMASI PRIBADI ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">🧍 Informasi Pribadi</h3>
                        <div class="row g-4">
                            <div class="col-md-5">
                                <label class="form-label required">Nama Lengkap</label>
                                <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"  value="{{ old('fullname') }}" required>
                                @error('fullname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Nama Panggilan</label>
                                <input type="text" name="nickname" class="form-control @error('nickname') is-invalid @enderror" value="{{ old('nickname') }}">
                                @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" 
                                        class="form-select @error('gender') is-invalid @enderror" >
                                    <option value="">-- Pilih --</option>
                                    <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror" value="{{ old('birth_place') }}">
                                @error('birth_place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Lahir</label>
                                            {{-- <input type="date" name="birth_date" class="form-control" required
                                                value="{{ old('birth_date') }}"
                                                pattern="\d{4}-\d{2}-\d{2}" placeholder="YYYY-MM-DD"> --}}
                                            <input 
                                                type="text" 
                                                id="tanggal_lahir" 
                                                name="birth_date" 
                                                class="form-control" 
                                                placeholder="Pilih tanggal lahir"
                                                value="{{ old('birth_date') }}"
                                            >
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Agama</label>
                                <select name="religion_id" class="form-select select2">
                                    <option value="">-- Pilih Agama --</option>
                                    @foreach($religions as $religion)
                                        <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                            {{ $religion->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('religion_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Nomor KTP</label>
                                <input type="text" name="identity_number" class="form-control @error('identity_number') is-invalid @enderror" value="{{ old('identity_number') }}">
                                @error('identity_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NPWP</label>
                                <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror" value="{{ old('npwp') }}">
                                @error('npwp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ========== SECTION 2: KONTAK & ALAMAT ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">📞 Kontak & Alamat</h3>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label required">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-4 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">Provinsi</label>
                                <select name="province_id" id="province" 
                                        class="form-select select2 @error('province_id') is-invalid @enderror">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Kabupaten/Kota</label>
                                <select name="city_id" id="city" 
                                        class="form-select select2 @error('city_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kota --</option>
                                </select>
                                @error('city_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Kecamatan</label>
                                <select name="district_id" id="district" 
                                        class="form-select select2 @error('district_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    
                                </select>
                                @error('district_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Kelurahan</label>
                                <select name="sub_district_id" id="sub_district" 
                                        class="form-select select2 @error('sub_district_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kelurahan --</option>
                                    
                                </select>
                                @error('sub_district_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Kode Pos</label>
                                <select name="postal_code_id" id="postal_code" 
                                        class="form-select select2 @error('postal_code_id') is-invalid @enderror">
                                    <option value="">-- Pilih Kode Pos --</option>
                                    
                                </select>
                                @error('postal_code_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">🏦 Data Bank</h3>
                        <p class="small text-muted mb-3">Diperlukan bila terjadi pengembalian dana</p>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label" for="bank_id">Nama Bank</label>
                                <select id="bank_id" name="bank_id" class="form-select select2">
                                    <option value="">Pilih Bank</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nomor Rekening</label>
                                <input type="text" id="account_number" name="account_number" class="form-control @error('account_number') is-invalid @enderror" value="{{ old('account_number') }}">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Atas Nama</label>
                                <input type="text" id="account_holder" name="account_holder" class="form-control @error('account_holder') is-invalid @enderror" value="{{ old('account_holder') }}">
                                @error('account_holder')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- ========== SECTION 4: DATA KEPEGAWAIAN ========== --}}
                    <div class="section-block mb-5">
                        <h3 class="fw-semibold mb-3 border-bottom pb-2">Data Affiliator</h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label required">ID Affiliator</label>
                                <input type="text" id="nia" name="nia" class="form-control" required readonly>
                                @error('nia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            {{-- <div class="col-md-4">
                                <label class="form-label required">Kategori affiliator</label>
                                <select name="category" class="form-select" value="{{ old('category') }}" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1">Individu</option>
                                    <option value="2">Perusahaan</option>
                                    <option value="3">Instansi</option>
                                    <option value="4">Lainnya</option>
                                </select>
                            </div> --}}
                            <div class="col-md-4">
                                <label class="form-label">Saldo</label>
                                <input type="number" id="saldo" name="saldo" class="form-control @error('saldo') is-invalid @enderror" value="{{ old('saldo') }}">
                                @error('saldo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Membership</label>
                                <select name="membership" class="form-select select2" value="{{ old('membership') }}">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="1" {{ old('membership') == '1' ? 'selected' : '' }}>Putih</option>
                                    <option value="2" {{ old('membership') == '2' ? 'selected' : '' }}>Biru</option>
                                    <option value="3" {{ old('membership') == '3' ? 'selected' : '' }}>Merah</option>
                                </select>
                            </div>
                            
                            <div class="col-md-10">
                                <label class="form-label required" for="role">Posisi :</label>
                                <select class="form-control select2" name="role[]" multiple required>
                                    @foreach ($externalRoles as $role)
                                        <option value="{{ $role }}"
                                            {{ in_array($role, $selectedRoles ?? []) ? 'selected' : '' }}>
                                            {{ $role }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- SUBMIT --}}
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

@push('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%'
            });
        });
    </script>

    <script>
document.addEventListener("DOMContentLoaded", function() {
    flatpickr("#tanggal_lahir", {
        dateFormat: "d-m-Y",     // format tampilan ke user
        locale: "id",            // bahasa Indonesia
        maxDate: "today",        // tidak bisa pilih masa depan
        allowInput: true         // tetap bisa diketik manual
    });
});
</script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const niaField = document.getElementById('nia');

        // Pastikan elemen NIC ada dulu
        if (!niaField) return;

        fetch('{{ route('affiliators.generateNia') }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.nia) {
                niaField.value = data.nia;
            }
        })
        .catch(error => console.error('❌ Error fetching NIA:', error));
    });
</script>



    <script>
        document.getElementById('photo').addEventListener('change', function (event) {
        const input = event.target;
        const file = input.files[0];
        const previewContainer = document.getElementById('previewImage');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Jika sebelumnya preview berupa ikon <div>, ganti jadi <img>
                if (previewContainer.tagName.toLowerCase() === 'div') {
                    const img = document.createElement('img');
                    img.id = 'previewImage';
                    img.src = e.target.result;
                    img.className = 'border rounded-3 shadow-sm';
                    img.width = 150;
                    img.height = 150;
                    img.style.objectFit = 'cover';
                    previewContainer.replaceWith(img);
                } else {
                    previewContainer.src = e.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
    </script>

    

<script>
    $('#province').change(function () {
    var id = $(this).val();
    $('#city').html('<option>Loading...</option>');
    $('#district').html('<option value="">-- Pilih kecamatan --</option>');
    $('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

    if (id) {
    $.get('/api/cities/' + id, function (data) {
    $('#city').empty().append('<option value="">-- Pilih city --</option>');
    $.each(data, function (i, city) {
        $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
            });
        });
        }
    });

    $('#city').change(function () {
    var id = $(this).val();
    $('#district').html('<option>Loading...</option>');
    $('#sub_district').html('<option value="">-- Pilih kelurahan --</option>');

    if (id) {
        $.get('/api/districts/' + id, function (data) {
            $('#district').empty().append('<option value="">-- Pilih kecamatan --</option>');
            $.each(data, function (i, district) {
                $('#district').append('<option value="' + district.id + '">' + district.name + '</option>');
                    });
                });
            }
        });

    $('#district').change(function () {
    var id = $(this).val();
    $('#sub_district').html('<option>Loading...</option>');

        if (id) {
            $.get('/api/sub_districts/' + id, function (data) {
                $('#sub_district').empty().append('<option value="">-- Pilih kelurahan --</option>');
                $.each(data, function (i, sub_district) {
                    $('#sub_district').append('<option value="' + sub_district.id + '">' + sub_district.name + '</option>');
                });
            });
        }
    });

    $('#sub_district').change(function () {
    var id = $(this).val();
    $('#postal_code').html('<option>Loading...</option>');

    if (id) {
        $.get('/api/postal_codes/' + id, function (data) {
            $('#postal_code').empty().append('<option value="">-- Pilih kode pos --</option>');
            $.each(data, function (i, postal_code) {
                $('#postal_code').append('<option value="' + postal_code.id + '">' + postal_code.postal_code + '</option>');
            });
        });
        }
    });
</script>

<script>
    $(document).ready(function () {
    // 🟢 Ambil nilai lama (old) dari Blade
    let oldProvince  = "{{ old('province_id') }}";
    let oldCity      = "{{ old('city_id') }}";
    let oldDistrict  = "{{ old('district_id') }}";
    let oldSub       = "{{ old('sub_district_id') }}";
    let oldPostal    = "{{ old('postal_code_id') }}";

    if (oldProvince) {
    setTimeout(() => {
        loadCities(oldProvince, oldCity, function () {
            if (oldCity) loadDistricts(oldCity, oldDistrict, function () {
                if (oldDistrict) loadSubDistricts(oldDistrict, oldSub, function () {
                    if (oldSub) loadPostalCodes(oldSub, oldPostal);
                });
            });
        });
    }, 200); // tunggu 200ms
}


    console.log({
    oldProvince,
    oldCity,
    oldDistrict,
    oldSub,
    oldPostal
});


    // 🔹 Event saat provinsi berubah
    $('#province').on('change', function () {
        loadCities(this.value);
    });

    // 🔹 Event saat kota berubah
    $('#city').on('change', function () {
        loadDistricts(this.value);
    });

    // 🔹 Event saat kecamatan berubah
    $('#district').on('change', function () {
        loadSubDistricts(this.value);
    });

    // 🔹 Event saat kelurahan berubah
    $('#sub_district').on('change', function () {
        loadPostalCodes(this.value);
    });

    // ==============================
// Fungsi AJAX versi sesuai route kamu
// ==============================
function loadCities(provinceId, selected = null, callback = null) {
    if (!provinceId) return;
    $.get(`/api/cities/${provinceId}`, function (data) {
        $('#city').empty().append('<option value="">-- Pilih Kota --</option>');
        $.each(data, function (i, city) {
            $('#city').append(
                `<option value="${city.id}" ${selected == city.id ? 'selected' : ''}>${city.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadDistricts(cityId, selected = null, callback = null) {
    if (!cityId) return;
    $.get(`/api/districts/${cityId}`, function (data) {
        $('#district').empty().append('<option value="">-- Pilih Kecamatan --</option>');
        $.each(data, function (i, district) {
            $('#district').append(
                `<option value="${district.id}" ${selected == district.id ? 'selected' : ''}>${district.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadSubDistricts(districtId, selected = null, callback = null) {
    if (!districtId) return;
    $.get(`/api/sub_districts/${districtId}`, function (data) {
        $('#sub_district').empty().append('<option value="">-- Pilih Kelurahan --</option>');
        $.each(data, function (i, sub) {
            $('#sub_district').append(
                `<option value="${sub.id}" ${selected == sub.id ? 'selected' : ''}>${sub.name}</option>`
            );
        });
        if (callback) callback();
    });
}

function loadPostalCodes(subId, selected = null) {
    if (!subId) return;
    $.get(`/api/postal_codes/${subId}`, function (data) {
        $('#postal_code').empty().append('<option value="">-- Pilih Kode Pos --</option>');
        $.each(data, function (i, code) {
            $('#postal_code').append(
                `<option value="${code.id}" ${selected == code.id ? 'selected' : ''}>${code.postal_code}</option>`
            );
        });
    });
}

});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Ambil old value dari Blade (jika ada)
    const oldBankId = "{{ old('bank_id') }}";

    fetch(`${window.location.origin}/api/banks`)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('bank_id');
            data.forEach(bank => {
                const option = document.createElement('option');
                option.value = bank.id;
                option.text = `${bank.name} (${bank.code})`;

                // Cek apakah bank.id sama dengan oldBankId
                if (bank.id == oldBankId) {
                    option.selected = true;
                }

                select.appendChild(option);

                $('#bank_id').val(oldBankId).trigger('change');
            });
        });
});
</script>

@endpush