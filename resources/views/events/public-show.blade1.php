@extends('layouts.website')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">

            <div class="col">
                <div class="d-flex align-items-center gap-3">

                    <a href="/"
                       class="btn btn-icon btn-secondary"
                       title="Kembali">
                        <i class="ti ti-arrow-left"></i>
                    </a>

                    <div>
                        <div class="text-secondary small mb-1">
                            EVENT
                        </div>

                        <h2 class="mb-0">
                            {{ $event->name }}
                        </h2>
                    </div>

                </div>
            </div>

            <div class="col-auto">
                <a href="{{ route('register', $event->id) }}"
                   class="btn btn-primary">
                    <i class="ti ti-ticket me-1"></i>
                    Daftar Sekarang
                </a>
            </div>

        </div>
    </div>


    {{-- =========================================================
        EVENT HERO
    ========================================================== --}}
    <div class="row g-4 mb-4">

        {{-- =====================================================
            LEFT CONTENT
        ====================================================== --}}
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


                    {{-- Event Name --}}
                    <h1 class="event-title">
                        {{ $event->name }}
                    </h1>


                    {{-- Event Code --}}
                    @if($event->event_code)

                        <div class="text-secondary mb-4">
                            <i class="ti ti-hash me-1"></i>
                            {{ $event->event_code }}
                        </div>

                    @endif


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


                    {{-- Price + Quota --}}
                    <div class="row g-3 mt-4">

                        {{-- Price --}}
                        <div class="col-sm-6">

                            <div class="event-stat-card">

                                <div class="event-stat-icon">
                                    Rp
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


                    {{-- Location --}}
                    <div class="event-location-card mt-3 mb-4">

                        <div class="event-stat-icon">
                            <i class="ti ti-map-pin"></i>
                        </div>

                        <div class="flex-grow-1">

                            <div class="event-stat-label">
                                Lokasi
                            </div>

                            <div class="event-location-value">
                                {{ $event->location ?: 'Lokasi belum ditentukan' }}
                            </div>

                        </div>

                        @if($event->google_maps_url)

                            <a href="{{ $event->google_maps_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="event-location-arrow"
                               aria-label="Buka lokasi di Google Maps">

                                <i class="ti ti-arrow-right"></i>

                            </a>

                        @endif

                    </div>


                    {{-- =================================================
                        REGISTRATION CTA
                    ================================================== --}}
                    <div class="card card-sm bg-primary-lt border-0 mb-4">

                        <div class="card-body">

                            <div class="row align-items-center g-3">

                                <div class="col">

                                    <div class="d-flex align-items-center gap-3">

                                        <span class="avatar avatar-lg bg-primary text-white">
                                            <i class="ti ti-ticket fs-2"></i>
                                        </span>

                                        <div>

                                            <div class="fw-bold fs-3">
                                                Jangan Lewatkan Event Ini
                                            </div>

                                            <div class="text-secondary">
                                                Daftarkan diri kamu sekarang dan
                                                jadilah bagian dari event ini.
                                            </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-auto">

                                    <a href="{{ route('register', $event->id) }}"
                                       class="btn btn-primary">

                                        <i class="ti ti-ticket me-1"></i>
                                        Daftar Sekarang

                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Thumbnail --}}
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


                    {{-- =================================================
                        FAQ
                    ================================================== --}}
                    <div class="card mb-4">

                        <div class="card-header">

                            <h3 class="card-title">
                                <i class="ti ti-help-circle me-2"></i>
                                FAQ
                            </h3>

                        </div>

                        <div class="card-body">

                            @if($event->faqs && $event->faqs->count())

                                <div class="accordion"
                                     id="eventFaqAccordion">

                                    @foreach($event->faqs as $index => $faq)

                                        <div class="accordion-item">

                                            <h2 class="accordion-header"
                                                id="faq-heading-{{ $index }}">

                                                <button
                                                    class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#faq-collapse-{{ $index }}"
                                                    aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                    aria-controls="faq-collapse-{{ $index }}">

                                                    {{ $faq->question }}

                                                </button>

                                            </h2>


                                            <div
                                                id="faq-collapse-{{ $index }}"
                                                class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                                aria-labelledby="faq-heading-{{ $index }}"
                                                data-bs-parent="#eventFaqAccordion">

                                                <div class="accordion-body">

                                                    {!! nl2br(e($faq->answer)) !!}

                                                </div>

                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            @else

                                <div class="event-gallery-empty">

                                    <i class="ti ti-help-circle-off"></i>

                                    <div class="fw-semibold mt-2">
                                        Belum ada FAQ
                                    </div>

                                    <div class="text-secondary small">
                                        FAQ untuk event ini belum ditambahkan.
                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
            RIGHT SIDEBAR
        ========================================================== --}}
        <div class="col-lg-4">


            {{-- =====================================================
                EVENT SCHEDULE
            ====================================================== --}}
            <div class="card mb-4">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="ti ti-clock me-2"></i>

                        Jadwal Event

                    </h3>

                </div>


                <div class="card-body">

                    <div class="event-timeline">


                        {{-- Start --}}
                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">

                                <i class="ti ti-calendar-plus"></i>

                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Mulai
                                </div>

                                @if($event->start_at)

                                    <div class="fw-semibold">

                                        {{ $event->start_at->translatedFormat('l, d F Y') }}

                                    </div>

                                    <div class="text-secondary">

                                        {{ $event->start_at->format('H:i') }}
                                        WIB

                                    </div>

                                @else

                                    <div class="text-secondary">
                                        -

                                    </div>

                                @endif

                            </div>

                        </div>


                        {{-- End --}}
                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">

                                <i class="ti ti-calendar-x"></i>

                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Selesai
                                </div>

                                @if($event->end_at)

                                    <div class="fw-semibold">

                                        {{ $event->end_at->translatedFormat('l, d F Y') }}

                                    </div>

                                    <div class="text-secondary">

                                        {{ $event->end_at->format('H:i') }}
                                        WIB

                                    </div>

                                @else

                                    <div class="text-secondary">
                                        -
                                    </div>

                                @endif

                            </div>

                        </div>


                    </div>

                </div>

            </div>


            {{-- =====================================================
                REGISTRATION PERIOD
            ====================================================== --}}
            <div class="card mb-4">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="ti ti-calendar-event me-2"></i>

                        Periode Pendaftaran

                    </h3>

                </div>


                <div class="card-body">

                    <div class="event-timeline">


                        {{-- Open --}}
                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">

                                <i class="ti ti-calendar-plus"></i>

                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Pendaftaran Dibuka
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


                        {{-- Close --}}
                        <div class="event-timeline-item">

                            <div class="event-timeline-icon">

                                <i class="ti ti-calendar-x"></i>

                            </div>

                            <div>

                                <div class="text-secondary small">
                                    Pendaftaran Ditutup
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


            {{-- =====================================================
                SPEAKERS
            ====================================================== --}}
            @if($event->speakers->count())

                <div class="card mb-4">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="ti ti-microphone-2 me-2"></i>

                            Pembicara

                        </h3>

                    </div>


                    <div class="card-body">

                        <div class="d-flex flex-column gap-3">

                            @foreach($event->speakers as $speaker)

                                <div class="d-flex align-items-center gap-3">

                                    <div class="avatar avatar-lg">

                                        @if($speaker->photo)

                                            <img
                                                src="{{ asset('storage/'.$speaker->photo) }}"
                                                alt="{{ $speaker->fullname }}">

                                        @else

                                            <i class="ti ti-user fs-2"></i>

                                        @endif

                                    </div>

                                    <div>

                                        <div class="fw-semibold">
                                            {{ $speaker->fullname }}
                                        </div>

                                        <div class="text-secondary small">
                                            Pembicara
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @endif


            {{-- =====================================================
                GALLERY
            ====================================================== --}}
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

                                    <img
                                        src="{{ Storage::url($gallery->image) }}"
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


            {{-- =====================================================
                YOUTUBE
            ====================================================== --}}
            @if($event->youtubeLinks->count())

                <div class="card mb-4">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="ti ti-brand-youtube me-2"></i>

                            Video Event

                        </h3>

                    </div>


                    <div class="card-body">

                        <div class="event-video-scroll">

                            @foreach($event->youtubeLinks as $link)

                                <div class="event-video-item">

                                    <div class="event-video-wrapper">

                                        @if($link->embed_url)

                                            <iframe
                                                src="{{ $link->embed_url }}"
                                                title="{{ $link->title ?? $event->name }}"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen
                                                loading="lazy">
                                            </iframe>

                                        @endif

                                    </div>


                                    @if($link->title)

                                        <div class="event-video-title">
                                            {{ $link->title }}
                                        </div>

                                    @endif

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
        RUNDOWN
    ========================================================== --}}
    <div class="card mb-4">

        <div class="card-header">

            <h3 class="card-title">

                <i class="ti ti-list-details me-2"></i>

                Rundown Acara {{ $event->name }}

            </h3>

        </div>


        <div class="card-body">

            @if($event->rundowns && $event->rundowns->count())

                @php

                    $groupedRundowns = $event->rundowns
                        ->sortBy([
                            ['rundown_date', 'asc'],
                            ['sort_order', 'asc'],
                            ['start_time', 'asc'],
                        ])
                        ->groupBy(function ($item) {

                            return \Carbon\Carbon::parse(
                                $item->rundown_date
                            )->format('Y-m-d');

                        });

                @endphp


                @foreach($groupedRundowns as $date => $items)

                    @if($groupedRundowns->count() > 1)

                        <div class="text-secondary small text-uppercase fw-semibold mb-2 mt-4">

                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}

                        </div>

                    @endif


                    <div class="event-timeline">

                        @foreach($items as $rundown)

                            <div class="event-timeline-item">

                                <div class="event-timeline-icon">

                                    <i class="ti ti-clock"></i>

                                </div>


                                <div>

                                    <div class="text-secondary small">

                                        {{ \Carbon\Carbon::parse($rundown->start_time)->format('H:i') }}

                                        @if($rundown->end_time)

                                            -
                                            {{ \Carbon\Carbon::parse($rundown->end_time)->format('H:i') }}

                                        @endif

                                        WIB

                                    </div>


                                    <div class="fw-semibold">

                                        {{ $rundown->activity }}

                                    </div>


                                    @if($rundown->description)

                                        <div class="text-secondary small mt-1">

                                            {{ $rundown->description }}

                                        </div>

                                    @endif


                                    <div class="d-flex flex-wrap gap-3 mt-1">

                                        @if($rundown->speaker && $rundown->speaker !== '-')

                                            <div class="text-secondary small">

                                                <i class="ti ti-microphone-2 me-1"></i>

                                                {{ $rundown->speaker }}

                                            </div>

                                        @endif


                                        @if($rundown->location)

                                            <div class="text-secondary small">

                                                <i class="ti ti-map-pin me-1"></i>

                                                {{ $rundown->location }}

                                            </div>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endforeach

            @else

                <div class="event-gallery-empty">

                    <i class="ti ti-list-details"></i>

                    <div class="fw-semibold mt-2">
                        Belum ada rundown
                    </div>

                    <div class="text-secondary small">
                        Rundown acara belum ditambahkan.
                    </div>

                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
        SPONSORSHIP
    ========================================================== --}}
    <div class="card mb-4">

        <div class="card-header">

            <div>

                <h3 class="card-title mb-1">

                    <i class="ti ti-heart-handshake me-2"></i>

                    Peluang Amal Shalih

                </h3>

                <div class="text-secondary small">

                    Sponsorship / Dukungan Acara

                </div>

            </div>

        </div>


        <div class="card-body">

            <div class="row g-4">


                {{-- =================================================
                    WHATSAPP
                ================================================== --}}
                <div class="col-lg-6">

                    <div class="card card-sm border h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center gap-3 mb-4">

                                <span class="avatar avatar-lg bg-success-lt">

                                    <i class="ti ti-brand-whatsapp fs-2"></i>

                                </span>


                                <div>

                                    <h3 class="mb-1">
                                        Hubungi WA Admin
                                    </h3>

                                    <div class="text-secondary">

                                        Hubungi admin untuk informasi
                                        sponsorship atau dukungan acara.

                                    </div>

                                </div>

                            </div>


                            @if($event->sponsorship_whatsapp)

                                @php

                                    $whatsappNumber = preg_replace(
                                        '/[^0-9]/',
                                        '',
                                        $event->sponsorship_whatsapp
                                    );

                                    if (str_starts_with($whatsappNumber, '0')) {

                                        $whatsappNumber =
                                            '62' . substr($whatsappNumber, 1);

                                    }

                                @endphp


                                <div class="text-secondary small mb-1">

                                    Nomor WhatsApp Admin

                                </div>


                                <div class="fw-semibold mb-3">

                                    {{ $event->sponsorship_whatsapp }}

                                </div>


                                <a href="https://wa.me/{{ $whatsappNumber }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-success">

                                    <i class="ti ti-brand-whatsapp me-1"></i>

                                    Hubungi Admin

                                </a>

                            @else

                                <div class="event-gallery-empty">

                                    <i class="ti ti-brand-whatsapp"></i>

                                    <div class="fw-semibold mt-2">

                                        WhatsApp Admin Belum Tersedia

                                    </div>

                                    <div class="text-secondary small">

                                        Informasi kontak sponsorship belum
                                        ditambahkan.

                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- =================================================
                    QRIS
                ================================================== --}}
                <div class="col-lg-6">

                    <div class="card card-sm border h-100">

                        <div class="card-body">

                            <div class="d-flex align-items-center gap-3 mb-4">

                                <span class="avatar avatar-lg bg-primary-lt">

                                    <i class="ti ti-qrcode fs-2"></i>

                                </span>


                                <div>

                                    <h3 class="mb-1">
                                        QRIS
                                    </h3>

                                    <div class="text-secondary">

                                        Dukungan acara melalui QRIS.

                                    </div>

                                </div>

                            </div>


                            @if($event->sponsorship_qris)

                                <div class="text-center">

                                    <div class="border rounded p-3 d-inline-block bg-white">

                                        <img
                                            src="{{ Storage::url($event->sponsorship_qris) }}"
                                            alt="QRIS {{ $event->name }}"
                                            class="img-fluid"
                                            style="
                                                max-width: 320px;
                                                max-height: 400px;
                                                object-fit: contain;
                                            ">

                                    </div>


                                    <div class="text-secondary small mt-3">

                                        Scan QRIS untuk memberikan dukungan
                                        terhadap acara ini.

                                    </div>

                                </div>

                            @else

                                <div class="event-gallery-empty">

                                    <i class="ti ti-qrcode-off"></i>

                                    <div class="fw-semibold mt-2">

                                        QRIS Belum Tersedia

                                    </div>

                                    <div class="text-secondary small">

                                        QRIS untuk dukungan acara belum
                                        ditambahkan.

                                    </div>

                                </div>

                            @endif

                        </div>

                    </div>

                </div>


            </div>

        </div>

    </div>
    <div class="card bg-primary text-white mb-4">

        <div class="card-body py-4">

            <div class="row align-items-center g-3">

                <div class="col">

                    <div class="d-flex align-items-center gap-3">

                        <span class="avatar avatar-lg bg-white text-primary">

                            <i class="ti ti-ticket fs-2"></i>

                        </span>


                        <div>

                            <h2 class="mb-1">
                                {{ $event->name }}
                            </h2>

                            <div class="opacity-75">
                                Yuk, daftar dan ikut berpartisipasi
                                dalam event ini.
                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-auto">

                    <a href="{{ route('register', $event->id) }}"
                       class="btn btn-light">

                        <i class="ti ti-ticket me-1"></i>

                        Daftar Sekarang

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection