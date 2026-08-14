@extends('tablar::page')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-auto ms-auto d-print-none">
                <div class="btn-list">
                {{-- @can('tambah data karyawan')        --}}
                <span class="d-none d-sm-inline">
                    <a href="{{ route("event_speakers.create") }}" class="btn btn-primary d-none d-sm-inline-block">
                        <!-- Download SVG icon from http://tabler-icons.io/i/plus -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Tambah Data Pembicara
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
                        <p class="text-center mb-4" style="font-size: 1.5rem; font-weight: 400; font-family: 'Poppins', sans-serif;">
                                Daftar Pembicara
                        </p>
                    </div>
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        
                                        <th>Nama Merk</th>
                                        <th>Asal pabrik</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($event_speakers as $speak)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            
                                            <td>{{ $speak->name }}</td>
                                            <td>{{ $speak->factory_origin }}</td>
                                            <td>
                                                <a href="{{ route('event_speakers.edit', $speak) }}" class="btn btn-sm btn-warning">Edit</a>
                                                <form action="{{ route('event_speakers.destroy', $speak) }}" method="POST" style="display:inline-block">
                                                    @csrf @method('DELETE')
                                                    <button onclick="return confirm('Delete this brand?')" class="btn btn-sm btn-danger">Delete</button>
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
@endsection
