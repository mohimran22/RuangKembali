@extends('tablar::auth.layout')
@section('title', 'Lupa Kata Kunci - Ruang Kembali')
@section('content')
<style>
    body {
        background: url('{{ asset('images/coba-hero.jpeg') }}') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Montserrat', sans-serif !important;
    }
    input, button { font-family: 'Montserrat', sans-serif !important; }
    .auth-wrapper {
        min-height: calc(100dvh - 90px);
        padding-top: 100px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }
    .login-panel {
        z-index: 2;
        background: rgba(255,255,255,0.95);
        border-radius: 10px;
        padding: 40px;
        width: 50%;
        max-width: 400px;
        margin: 5%;
        box-shadow: 0 8px 25px rgba(0,0,0,.15);
    }
    .login-panel h3 { font-weight: 900; text-align: center; font-size: 24px; margin-bottom: 1rem; }
    .form-control { border-radius: 10px; padding: .75rem 1rem; }
    .btn-login { background:#DCCBA8; border:none; color:#111; border-radius:10px; padding:12px 20px; font-weight:600; transition:.3s; }
    .btn-login:hover { background:#c3bbb0; transform:translateY(-2px); box-shadow:0 10px 20px rgba(0,0,0,.15); }
    @media (max-width: 1200px) {
        .auth-wrapper { justify-content:center; flex-direction:column; padding:120px 24px 40px; }
        .login-panel { width:100%; max-width:600px; margin:0; }
    }
</style>

<div class="auth-wrapper">
    <div class="login-panel">
        <h3>Lupa Kata Kunci</h3>
        <p class="text-muted small text-center mb-4">
            Masukkan email yang terdaftar. Kami akan mengirimkan link untuk mengatur ulang kata kunci.
        </p>

        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-mail"></i></span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-control border-start-0 @error('email') is-invalid @enderror"
                           placeholder="Masukkan email Anda" autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="cf-turnstile"
                     data-sitekey="{{ config('services.turnstile.site_key') }}"
                     data-size="flexible" data-theme="light"></div>
                @error('cf-turnstile-response')
                    <div class="invalid-feedback d-block text-center">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-login text-white w-100">Kirim Link Reset</button>

            <div class="text-center mt-3 small">
                <a href="{{ route('login') }}" class="fw-semibold text-dark">Kembali ke halaman masuk</a>
            </div>
        </form>
    </div>
</div>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endsection