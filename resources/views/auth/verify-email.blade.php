@extends('tablar::auth.layout')

@section('content')

<div class="page-body">
    <div class="container-xl">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">

                        <div class="mb-4 text-secondary">
                            {{ __('Terima kasih sudah mendaftar! Sebelum melanjutkan, silakan verifikasi alamat email kamu dengan mengklik link yang sudah kami kirimkan. Jika belum menerima email, kami akan kirimkan lagi.') }}
                        </div>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success">
                                {{ __('Link verifikasi baru sudah dikirim ke alamat email yang kamu daftarkan.') }}
                            </div>
                        @endif

                        <div class="d-flex align-items-center justify-content-between mt-4">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-dark">
                                    {{ __('Kirim Ulang Email Verifikasi') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-link text-secondary">
                                    {{ __('Keluar') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection