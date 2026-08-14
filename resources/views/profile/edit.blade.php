@extends('tablar::page')

@section('content')
<style>
.profile-card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 12px 35px rgba(0,0,0,.08);
}

.profile-card .card-header{
    background:#fff;
    border-bottom:1px solid #f0f0f0;
    padding:22px 28px;
}

.profile-card .card-header h3{
    font-size:20px;
    font-weight:600;
    color:#111;
}

.profile-card .card-body{
    padding:28px;
}

.form-label{
    font-weight:500;
    margin-bottom:.45rem;
}

.form-control{
    height:48px;
    border-radius:12px;
    border:1px solid #e8e8e8;
}

.form-control:focus{
    border-color:#d8c08f;
    box-shadow:0 0 0 .18rem rgba(220,203,168,.25);
}

.input-group-text{
    border-radius:0 12px 12px 0;
    background:#fff;
}

.btn-save{
    background:#DCCBA8;
    color:#111;
    border:none;
    border-radius:12px;
    height:46px;
    padding:0 30px;
    font-weight:600;
}

.btn-save:hover{
    background:#ccb88d;
}
</style>
<div class="container-fluid" style="padding-top:90px;">
    <div class="row">
        <!-- Form Update Profil -->
        <div class="col-xl-7 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h3>
                        <i class="ti ti-user-circle me-2"></i>
                        Informasi Profil
                    </h3>

                    <small class="text-muted">
                        Kelola informasi akun dan keamanan Anda
                    </small>
                </div>
                <div class="card-body">
                    {{-- <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form> --}}

                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <!-- Nama -->
                        <div class="mb-3">
                            <label for="fullname" class="form-label">Nama</label>
                            <input id="fullname" name="fullname" type="text"
                                class="form-control @error('fullname') is-invalid @enderror"
                                value="{{ old('fullname', $user->fullname) }}" required autofocus autocomplete="fullname">
                            @error('fullname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}" required autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Saat Ini -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                            <div class="input-group">
                                <input id="current_password" name="current_password" type="password"
                                    class="form-control @error('current_password') is-invalid @enderror"
                                    placeholder="Masukkan kata sandi lama" autocomplete="current-password">
                                <span class="input-group-text">
                                    <a href="#" class="toggle-password link-secondary"
                                       data-toggle="tooltip" data-target="current_password"
                                       title="Show password" aria-label="Show password">
                                        <svg class="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="2"/>
                                            <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7
                                                     c2.667-4.667 6-7 10-7s7.333 2.333 10 7"/>
                                        </svg>
                                        <svg class="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" style="display:none;"
                                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3l18 18"/>
                                        </svg>
                                    </a>
                                </span>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Baru -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Kata Sandi Baru</label>
                            <div class="input-group">
                                <input id="password" name="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="Masukkan kata sandi baru" autocomplete="new-password">
                                <span class="input-group-text">
                                    <a href="#" class="toggle-password link-secondary"
                                       data-toggle="tooltip" data-target="password"
                                       title="Show password" aria-label="Show password">
                                        <svg class="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="2"/>
                                            <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7
                                                     c2.667-4.667 6-7 10-7s7.333 2.333 10 7"/>
                                        </svg>
                                        <svg class="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" style="display:none;"
                                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3l18 18"/>
                                        </svg>
                                    </a>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                            <div class="input-group">
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    placeholder="Ulangi kata sandi baru" autocomplete="new-password">
                                <span class="input-group-text">
                                    <a href="#" class="toggle-password link-secondary"
                                       data-toggle="tooltip" data-target="password_confirmation"
                                       title="Show password" aria-label="Show password">
                                        <svg class="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="2"/>
                                            <path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7
                                                     c2.667-4.667 6-7 10-7s7.333 2.333 10 7"/>
                                        </svg>
                                        <svg class="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" style="display:none;"
                                            width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3l18 18"/>
                                        </svg>
                                    </a>
                                </span>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button class="btn btn-save">
                            <i class="ti ti-device-floppy me-1"></i>
                            Simpan Perubahan
                        </button>

                        @if (session('status') === 'profile-updated')
                            <div class="alert alert-success mt-3">
                                <i class="ti ti-check"></i> Profil berhasil diperbarui!
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script>
    $(function () {
        // Tooltip init (Bootstrap 4 pakai data-toggle, bukan data-bs-toggle)
        $('[data-toggle="tooltip"]').tooltip();

        // Show/hide password toggle
        $('.toggle-password').on('click', function (e) {
            e.preventDefault();

            const targetId = $(this).data('target');   // ambil ID input
            const $input = $('#' + targetId);

            const isPassword = $input.attr('type') === 'password';
            $input.attr('type', isPassword ? 'text' : 'password');

            // toggle icon
            $(this).find('.eyeIcon').toggle(!isPassword);
            $(this).find('.eyeOffIcon').toggle(isPassword);

            // update tooltip title
            const title = isPassword ? 'Hide password' : 'Show password';
            $(this)
                .attr('title', title)
                .tooltip('dispose') // reset tooltip lama
                .tooltip();         // aktifkan tooltip baru
        });
    });
</script>
@endpush

