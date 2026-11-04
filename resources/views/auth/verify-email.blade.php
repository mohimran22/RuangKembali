@extends('tablar::auth.layout')

@section('content')

<style>
    /* Sesuaikan --nav-offset kalau tinggi navbar berubah */
    .verify-wrapper {
        --nav-offset: 140px;
        min-height: 100vh;
        min-height: 100dvh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: var(--nav-offset) 16px 48px;
    }

    .verify-card {
        width: 100%;
        max-width: 520px;
        background: #fff;
        border-radius: 20px;
        padding: 40px 36px 32px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, .08);
        text-align: center;
    }

    .verify-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #f3ead6;
        color: #1f2937;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .verify-card h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 12px;
    }

.verify-card p,
.verify-card .alert {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif !important;
    font-weight: 400 !important;
    font-size: .95rem;
    line-height: 1.6;
    color: #6b7280;
    margin-bottom: 8px;
}

    .verify-email {
        display: inline-block;
        margin: 4px 0 16px;
        padding: 6px 14px;
        border-radius: 999px;
        background: #f3f4f6;
        color: #111827;
        font-weight: 400;
        font-size: .9rem;
        word-break: break-all;
    }

    .verify-card .alert {
        font-weight: 400;
        font-size: .9rem;
        text-align: left;
        border-radius: 12px;
    }

    .verify-actions {
        margin-top: 24px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .verify-actions .btn-dark {
        width: 100%;
        padding: 12px 16px;
        border-radius: 12px;
        font-weight: 400;
    }

    .verify-actions .btn-link {
        font-weight: 400;
        color: #6b7280;
        text-decoration: none;
    }

    .verify-actions .btn-link:hover {
        color: #111827;
    }

    @media (max-width: 576px) {
        .verify-wrapper { --nav-offset: 120px; }
        .verify-card { padding: 32px 22px 24px; }
    }
</style>

<div class="verify-wrapper">
    <div class="verify-card">

        <div class="verify-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                <path d="M3 7l9 6l9 -6"></path>
            </svg>
        </div>

        <h2>Verifikasi Email Kamu</h2>

        <p>
            Terima kasih sudah mendaftar! Kami sudah mengirim link verifikasi ke:
        </p>

        <div class="verify-email">{{ auth()->user()->email }}</div>

        <p>
            Klik link tersebut untuk mengaktifkan akun. Belum menerima email?
            Cek folder spam, atau kirim ulang di bawah ini.
        </p>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success mt-3 mb-0" role="alert">
                Link verifikasi baru sudah dikirim ke alamat email yang kamu daftarkan.
            </div>
        @endif

        <div class="verify-actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-dark">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link">
                    Keluar
                </button>
            </form>
        </div>

    </div>
</div>

@endsection