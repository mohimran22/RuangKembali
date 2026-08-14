@extends('tablar::page')

@section('content')

<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">

            <div class="col">
                <div class="d-flex align-items-center gap-2">

                    <a href="{{ route('events.index') }}"
                       class="btn btn-icon btn-outline-secondary"
                       title="Kembali">
                        <i class="ti ti-arrow-left"></i>
                    </a>

                    <div>
                        <h2 class="page-title mb-1">
                            Detail Event
                        </h2>
                    </div>

                </div>
            </div>

            <div class="col-auto">
                <div class="btn-list">

                    <a href="{{ route('events.edit', $event->id) }}"
                       class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>
                        Daftar Sekarang
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle"
                                data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end">

                            <a href="{{ route('events.edit', $event->id) }}"
                               class="dropdown-item">
                                <i class="ti ti-edit me-2"></i>
                                Edit Event
                            </a>

                            <button type="button"
                                    class="dropdown-item text-danger"
                                    onclick="confirmDelete()">
                                <i class="ti ti-trash me-2"></i>
                                Hapus Event
                            </button>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-lg-8">

            <div class="card event-hero-card">

                {{-- Poster --}}
                <div class="event-poster-wrapper">

                    @if($event->poster)

                        <img src="{{ Storage::url($event->poster) }}"
                             alt="{{ $event->name }}"
                             class="event-poster">

                    @else

                        <div class="event-poster-placeholder">
                            <i class="ti ti-photo"></i>
                            <span>Tidak ada poster</span>
                        </div>

                    @endif

                </div>


                <div class="card-body">

                    {{-- Category + Type --}}
                    <div class="d-flex flex-wrap gap-2 mb-3">

                        @if($event->category)
                            <span class="badge bg-primary-lt">
                                <i class="ti ti-category me-1"></i>
                                {{ $event->category->name }}
                            </span>
                        @endif

                        @if($event->event_type === 'free')

                            <span class="badge bg-success-lt">
                                <i class="ti ti-gift me-1"></i>
                                Gratis
                            </span>

                        @else

                            <span class="badge bg-warning-lt">
                                <i class="ti ti-ticket me-1"></i>
                                Berbayar
                            </span>

                        @endif

                    </div>


                    {{-- Nama Event --}}
                    <h1 class="event-title">
                        {{ $event->name }}
                    </h1>


                    {{-- Event Code --}}
                    <div class="text-secondary mb-4">
                        <i class="ti ti-hash me-1"></i>
                        {{ $event->event_code }}
                    </div>


                    {{-- Description --}}
                    @if($event->description)

                        <div class="event-description">
                            {!! nl2br(e($event->description)) !!}
                        </div>

                    @else

                        <div class="text-secondary fst-italic">
                            Belum ada deskripsi event.
                        </div>

                    @endif


                    {{-- Main Stats --}}
                    <div class="row g-3 mt-4">

                        {{-- Price --}}
                        <div class="col-sm-6">

                            <div class="event-stat-card">

                                <div class="event-stat-icon">
                                    <i class="ti ti-currency-dollar"></i>
                                </div>

                                <div>
                                    <div class="event-stat-label">
                                        Harga Tiket
                                    </div>

                                    <div class="event-stat-value">

                                        @if($event->event_type === 'free')

                                            Gratis

                                        @else

                                            Rp {{ number_format($event->price ?? 0, 0, ',', '.') }}

                                        @endif

                                    </div>
                                </div>

                            </div>

                        </div>


                        {{-- Quota --}}
                        <div class="col-sm-6">

                            <div class="event-stat-card">

                                <div class="event-stat-icon">
                                    <i class="ti ti-users"></i>
                                </div>

                                <div>
                                    <div class="event-stat-label">
                                        Kuota Peserta
                                    </div>

                                    <div class="event-stat-value">

                                        @if($event->quota)
                                            {{ number_format($event->quota) }} Peserta
                                        @else
                                            Tidak Terbatas
                                        @endif

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <div class="event-location-card mt-3 mb-4">

                        <div class="event-stat-icon">
                            <i class="ti ti-map-pin"></i>
                        </div>

                        <div>
                            <div class="event-stat-label">
                                Lokasi
                            </div>

                            <div class="event-location-value">
                                {{ $event->location ?: 'Lokasi belum ditentukan' }}
                            </div>
                        </div>

                    </div>
                    @if($event->thumbnail)

                        <div class="card mb-4">

                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-photo me-2"></i>
                                    Media
                                </h3>
                            </div>

                            <div class="card-body">

                                <img src="{{ Storage::url($event->thumbnail) }}"
                                    alt="{{ $event->name }}"
                                    class="event-thumbnail">

                            </div>

                        </div>

                    @endif
                </div>

            </div>

        </div>

        <div class="col-lg-4">

            {{-- Status --}}
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        Status Event
                    </h3>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span class="text-secondary">
                            Publikasi
                        </span>

                        @if($event->is_published)

                            <span class="badge bg-success-lt">
                                <i class="ti ti-circle-check me-1"></i>
                                Published
                            </span>

                        @else

                            <span class="badge bg-secondary-lt">
                                <i class="ti ti-eye-off me-1"></i>
                                Belum Publish
                            </span>

                        @endif

                    </div>


                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <span class="text-secondary">
                            Jenis Event
                        </span>

                        @if($event->event_type === 'free')

                            <span class="badge bg-success-lt">
                                Gratis
                            </span>

                        @else

                            <span class="badge bg-warning-lt">
                                Berbayar
                            </span>

                        @endif

                    </div>


                    <div class="d-flex justify-content-between align-items-center">

                        <span class="text-secondary">
                            Audience
                        </span>

                        <span class="fw-semibold">
                            @switch($event->audience_type)

                                @case('public')
                                    Umum
                                    @break

                                @case('gender')
                                    Berdasarkan Gender
                                    @break

                                @case('age')
                                    Berdasarkan Usia
                                    @break

                                @default
                                    -
                            @endswitch
                        </span>

                    </div>

                </div>

            </div>


            {{-- Registration --}}
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-calendar-event me-2"></i>
                        Registrasi
                    </h3>
                </div>

                <div class="card-body">

                    <div class="event-timeline">

                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">
                                <i class="ti ti-calendar-plus"></i>
                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Registrasi Dibuka
                                </div>

                                <div class="fw-semibold">
                                    @if($event->registration_open)
                                        {{ $event->registration_open->translatedFormat('d F Y') }}
                                    @else
                                        -
                                    @endif
                                </div>

                            </div>

                        </div>

                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">
                                <i class="ti ti-calendar-x"></i>
                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Registrasi Ditutup
                                </div>

                                <div class="fw-semibold">
                                    @if($event->registration_close)
                                        {{ $event->registration_close->translatedFormat('d F Y') }}
                                    @else
                                        -
                                    @endif
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Schedule --}}
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-clock me-2"></i>
                        Jadwal Event
                    </h3>
                </div>

                <div class="card-body">

                    <div class="mb-3">

                        <div class="text-secondary small mb-1">
                            Mulai
                        </div>

                        <div class="fw-semibold">
                            {{ $event->start_at->translatedFormat('d F Y') }}
                        </div>

                        <div class="text-secondary">
                            {{ $event->start_at->format('H:i') }} WIB
                        </div>

                    </div>

                    <div>

                        <div class="text-secondary small mb-1">
                            Selesai
                        </div>

                        <div class="fw-semibold">
                            {{ $event->end_at->translatedFormat('d F Y') }}
                        </div>

                        <div class="text-secondary">
                            {{ $event->end_at->format('H:i') }} WIB
                        </div>

                    </div>

                </div>

            </div>
            <div class="card mb-4">

                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-photo me-2"></i>
                        Galeri Event
                    </h3>
                </div>

                <div class="card-body">

                    @if($event->galleries->count())

                        <div class="event-gallery-scroll">

                            @foreach($event->galleries as $gallery)

                                <div class="event-gallery-item">

                                    <img src="{{ Storage::url($gallery->image) }}"
                                        alt="{{ $gallery->caption ?? $event->name }}"
                                        class="event-gallery-image"
                                        loading="lazy">

                                    @if($gallery->caption)

                                        <div class="event-gallery-caption">
                                            {{ $gallery->caption }}
                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    @else

                        {{-- Empty State --}}
                        <div class="event-gallery-empty">

                            <i class="ti ti-photo-off"></i>

                            <div class="fw-semibold mt-2">
                                Belum ada galeri
                            </div>

                            <div class="text-secondary small">
                                Belum ada foto dokumentasi event.
                            </div>

                        </div>

                    @endif

                </div>

            </div>
                @if($event->youtube_url)

                    <div class="card mb-4">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="ti ti-brand-youtube me-2"></i>
                                Video Event
                            </h3>

                        </div>

                        <div class="card-body p-0">

                            <div class="event-video-wrapper">

                                <iframe
                                    src="{{ $event->youtube_embed_url }}"
                                    title="{{ $event->name }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>

                            </div>

                        </div>

                    </div>

                @endif
        </div>

    </div>
</div>

<form id="delete-form"
      action="{{ route('events.destroy', $event->id) }}"
      method="POST"
      class="d-none">

    @csrf
    @method('DELETE')

</form>


@endsection