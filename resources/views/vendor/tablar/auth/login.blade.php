@extends('tablar::auth.layout')
@section('title', 'Ruang Kembali')
@section('content')
<style>
    body {
        background: url('{{ asset('images/coba-hero.jpeg') }}') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 400 !important;
        background-color: #f8f9fc;
    }

    input, select, textarea, button {
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 400 !important;
    }

    .auth-wrapper {
        min-height:calc(100dvh - 90px);
        padding-top:100px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        position: relative;
    }

    .overlay-dark {
        position: absolute;
        inset: 0;
        z-index: 1;
    }

    .login-panel {
        z-index: 2;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 10px;
        padding: 40px;
        width: 50%;
        max-width: 400px;
        margin: 5% 5%;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .login-panel h3 {
        font-weight: 900;
        margin-bottom: 1.5rem;
        font-size: 24px;
        text-align: center;
    }

    .form-control {
        border-radius: 10px;
        font-weight: 400;
        padding: 0.75rem 1rem;
    }

    .btn-login {
        /* background: linear-gradient(180deg,#E7D7AE,#D2B57C); */
        background:#DCCBA8;
        border: none;
        color:#111;
        border-radius:10px;
        padding:12px 20px;
        font-weight:600;
        transition:.3s;
    }

    .btn-login:hover {
        background:#c3bbb0;
        transform:translateY(-2px);
        box-shadow:0 10px 20px rgba(0,0,0,.15);
    }
    .website-menu{
        display:flex;
        justify-content: space-between;
        align-items:center;
        gap:38px;
    }
    .website-icons{
        display:none;
    }
    @media (max-width: 1200px) {
        body {
            background-position: center;
        }

        .auth-wrapper {
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 120px 24px 40px;
            gap: 28px;
        }

       .login-panel {
            width: 100%;
            max-width: 600px;
            margin-right: 0;
            margin-top: 0;
            padding: 35px 30px;
        }
        .website-header{
            display:block;
        }
    }

    @media (max-width: 576px) {
        .page {
            margin-left: 0 !important;
        }
        body {

            background: url('{{ asset('LOGIN-mobile.jpeg') }}') no-repeat center center fixed;
            background-size: cover;
            background-position: center;
        }

        .auth-wrapper{
            min-height:calc(100dvh - 90px);
            padding-top:100px;     /* lebih baik daripada 100vh di mobile */
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
            position:relative;
        }

        .overlay-dark {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .login-panel {
            width: 100%;
            max-width: 360px;
            padding: 28px 22px;
            border-radius: 18px;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
            transform: translateX(1px);
        }

        .login-panel h3 {
            font-size: 24px;
            margin-bottom: 1.3rem;
            text-align: center;
        }

        .form-control {

            padding: 0.85rem 1rem;
            font-weight: 400;
            font-size: 15px;
        }

        .input-group-text {

            padding-left: 14px;
            padding-right: 14px;
        }

        .btn-login {

            padding: 0.85rem;

            border-radius: 12px;

            font-size: 15px;
        }

        .small {

            font-size: 12px !important;
        }
        .website-header{
            display:block;
        }
        .user-dropdown{
            display:none;
        }
    }
</style>

<div class="auth-wrapper">
    <div class="overlay-dark"></div>

    <div class="login-panel">
        <h3>Masuk</h3>

        <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="ti ti-mail"></i>
                    </span>
                    <input id="email" type="text" name="email"
                           class="form-control border-start-0 @error('email') is-invalid @enderror"
                           placeholder="Masukkan email Anda" autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Kata kunci</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="ti ti-lock"></i>
                    </span>
                    <input id="password" type="password" name="password"
                           class="form-control border-start-0 @error('password') is-invalid @enderror"
                           placeholder="Masukkan kata kunci">
                    <button type="button" id="togglePassword" class="btn btn-light border">
                        <i class="ti ti-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-login text-white w-100">Masuk</button>

            {{-- <div class="text-center mt-3 small">
                Lupa Password? Tenang, <a href="{{ route('password.request') }}" class="fw-semibold text-dark">klik disini!</a>
            </div> --}}

            <div class="text-center mt-3 small">
                Atau belum punya akun? <a href="{{ route('register') }}" class="fw-semibold text-dark">Daftar sekarang!</a>
            </div>

            <div class="text-center text-muted mt-4 small">
                Ruang Kembali © {{ date('Y') }} Semua Hak Dilindungi
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle password show/hide
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.innerHTML = type === 'password' 
            ? '<i class="ti ti-eye"></i>' 
            : '<i class="ti ti-eye-off"></i>';
    });
</script>
@endsection
