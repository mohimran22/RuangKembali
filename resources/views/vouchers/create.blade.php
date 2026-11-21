@extends('tablar::page')

@section('content')
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row align-items-center">
                <div class="col d-flex align-items-center">
                    <a href="{{ route('vouchers.index') }}" class="btn btn-primary d-flex align-items-center">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                    
                        <h2 class="page-title mb-0">Tambah Voucher</h2>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <form action="{{ route('vouchers.store') }}" method="POST">
                @csrf
                @include('vouchers._form', ['voucher' => null, 'locked' => false])
            </form>
        </div>
    </div>
@endsection