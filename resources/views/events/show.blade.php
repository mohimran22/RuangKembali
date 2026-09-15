@extends('tablar::page')

@section('content')

<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">

            <div class="col">
                <div class="d-flex align-items-center gap-3">

                    <a href="{{ route('events.index') }}"
                       class="btn btn-icon btn-secondary"
                       title="Kembali">
                        <i class="ti ti-arrow-left"></i>
                    </a>

                    <div>
                        <h2 class="mb-1">
                            Detail Event
                        </h2>
                    </div>

                </div>
            </div>

            <div class="col-auto">
                <div class="btn-list">

                    <a href="{{ route('events.register', $event->id) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-primary">

                        <i class="ti ti-ticket me-1"></i>
                        Daftar Sekarang

                    </a>
                    @can('tambah data event')
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
                    @endcan
                </div>
            </div>

        </div>
    </div>
    <div class="event-tabs-wrapper">
        <ul class="nav nav-tabs mb-4" id="eventDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active"
                        id="detail-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#detail-pane"
                        type="button"
                        role="tab"
                        aria-controls="detail-pane"
                        aria-selected="true">
                    <i class="ti ti-info-circle me-1"></i>
                    Detail Event
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="rundown-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#rundown-pane"
                        type="button"
                        role="tab"
                        aria-controls="rundown-pane"
                        aria-selected="false">
                    <i class="ti ti-list-details me-1"></i>
                    Rundown
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="sponsorship-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#sponsorship-pane"
                        type="button"
                        role="tab"
                        aria-controls="sponsorship-pane"
                        aria-selected="false">

                    <i class="ti ti-heart-handshake me-1"></i>
                    Sponsorship / Dukungan

                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="participants-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#participants-pane"
                        type="button"
                        role="tab"
                        aria-controls="participants-pane"
                        aria-selected="false">

                    <i class="ti ti-users me-1"></i>
                    Daftar Peserta

                </button>
            </li>
        </ul>
    </div>
    <div class="tab-content" id="eventDetailTabsContent">

        <div class="tab-pane fade show active"
            id="detail-pane"
            role="tabpanel"
            aria-labelledby="detail-tab"
            tabindex="0">

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
                            <div class="row g-3 mt-4">
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

                                                    {{ number_format($event->price ?? 0, 0, ',', '.') }}

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

                                <div class="flex-grow-1">
                                    <div class="event-stat-label">
                                        Lokasi
                                    </div>

                                    <div class="event-location-value">
                                        {{ $event->location ?: 'Lokasi belum ditentukan' }}
                                    </div>
                                </div>

                                @if ($event->google_maps_url)
                                    <a href="{{ $event->google_maps_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="event-location-arrow"
                                    aria-label="Buka lokasi di Google Maps">
                                        <i class="ti ti-arrow-right"></i>
                                    </a>
                                @endif

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

                            <div class="card mb-4">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="ti ti-help-circle me-2"></i>
                                        FAQ
                                    </h3>
                                </div>

                                <div class="card-body">

                                    @if($event->faqs && $event->faqs->count())

                                        <div class="accordion" id="eventFaqAccordion">

                                            @foreach($event->faqs as $index => $faq)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="faq-heading-{{ $index }}">
                                                        <button
                                                            class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                                                            type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#faq-collapse-{{ $index }}"
                                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                                            aria-controls="faq-collapse-{{ $index }}"
                                                        >
                                                            {{ $faq->question }}
                                                        </button>
                                                    </h2>

                                                    <div
                                                        id="faq-collapse-{{ $index }}"
                                                        class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                                        aria-labelledby="faq-heading-{{ $index }}"
                                                        data-bs-parent="#eventFaqAccordion"
                                                    >
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
                    @if($event->speakers->count())

                        <div class="card mb-4">

                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="ti ti-microphone-2 me-2"></i>
                                    Pembicara
                                </h3>
                            </div>

                            <div class="card-body">

                                <div class="d-flex flex-wrap gap-3">

                                    @foreach($event->speakers as $speaker)

                                        <div class="event-speaker-item">

                                            <div class="event-speaker-avatar">
                                                @if($speaker->photo)
                                                    <img src="{{ asset('storage/'.$speaker->photo) }}"
                                                        alt="{{ $speaker->fullname }}">
                                                @else
                                                    <i class="ti ti-user"></i>
                                                @endif
                                            </div>

                                            <div class="fw-semibold">
                                                {{ $speaker->fullname }}
                                            </div>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                    @endif
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

        <div class="tab-pane fade"
            id="rundown-pane"
            role="tabpanel"
            aria-labelledby="rundown-tab"
            tabindex="0">

            <div class="card">

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
                                    return \Carbon\Carbon::parse($item->rundown_date)->format('Y-m-d');
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
                                                    - {{ \Carbon\Carbon::parse($rundown->end_time)->format('H:i') }}
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

        </div>
        <div class="tab-pane fade"
            id="sponsorship-pane"
            role="tabpanel"
            aria-labelledby="sponsorship-tab"
            tabindex="0">

            <div class="card">

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

                        {{-- WhatsApp Admin --}}
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
                                                $whatsappNumber = '62' . substr($whatsappNumber, 1);
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


                        {{-- QRIS --}}
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

                                                <img src="{{ Storage::url($event->sponsorship_qris) }}"
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

        </div>
        </div>
        <div class="tab-pane fade"
            id="participants-pane"
            role="tabpanel"
            aria-labelledby="participants-tab"
            tabindex="0">

            <div class="card">
{{-- 
                <div class="card-header">
                    <div>
                        <h3 class="card-title mb-1">
                            <i class="ti ti-users me-2"></i>
                            Daftar Peserta Event {{ $event->name }}
                        </h3>

                        <div class="text-secondary small">
                            Peserta yang telah melakukan pendaftaran pada event ini.
                        </div>
                    </div>

                    <div class="ms-auto d-flex align-items-center gap-2">

                        @hasanyrole(['Tim', 'Super-Admin'])

                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#checkinModal">
                                <i class="ti ti-scan me-1"></i>
                                Check-in Peserta
                            </button>

                        @endhasanyrole

                        <span class="badge bg-primary-lt">
                            {{ $registrations->count() }} Peserta
                        </span>
                    </div>
                </div> --}}
                <div class="card-header flex-column flex-md-row align-items-start align-items-md-center gap-2">

    <div>
        <h3 class="card-title mb-1">
            <i class="ti ti-users me-2"></i>
            Daftar Peserta Event {{ $event->name }}
        </h3>

        <div class="text-secondary small">
            Peserta yang telah melakukan pendaftaran pada event ini.
        </div>
    </div>

    <div class="w-100 w-md-auto ms-md-auto d-flex align-items-center justify-content-between gap-2">

        @hasanyrole(['Tim', 'Super-Admin'])

            <button type="button"
                    class="btn btn-success btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#checkinModal">
                <i class="ti ti-scan me-1"></i>
                Check-in Peserta
            </button>

        @endhasanyrole

        <span class="badge bg-primary-lt">
            {{ $registrations->count() }} Peserta
        </span>

    </div>

</div>

                <div class="card-body">

                    @if($registrations->count())

                        <div class="table-responsive">

                            <table class="table table-vcenter">

                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Peserta</th>
                                        <th>Pembayaran</th>
                                        <th>Waktu terdaftar</th>
                                        <th>Status Check-in</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($registrations as $index => $registration)

                                        <tr data-registration-id="{{ $registration->id }}"
                                            data-ticket-code="{{ $registration->ticket_code }}">

                                            <td>
                                                {{ $index + 1 }}
                                            </td>

                                            <td>
                                                <div class="d-flex align-items-center">

                                                    <span class="avatar me-2">
                                                        <i class="ti ti-user"></i>
                                                    </span>

                                                    <div>
                                                        <div class="fw-semibold">
                                                            {{ $registration->participant_name }}
                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                            <td>

                                                @if($registration->status === 'confirmed')

                                                    <span class="badge bg-success-lt">
                                                        <i class="ti ti-circle-check me-1"></i>
                                                        Confirmed
                                                    </span>

                                                @elseif($registration->status === 'pending')

                                                    <span class="badge bg-warning-lt">
                                                        <i class="ti ti-clock me-1"></i>
                                                        Pending
                                                    </span>

                                                @elseif($registration->status === 'cancelled')

                                                    <span class="badge bg-danger-lt">
                                                        <i class="ti ti-circle-x me-1"></i>
                                                        Cancelled
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary-lt">
                                                        {{ ucfirst($registration->status ?? '-') }}
                                                    </span>

                                                @endif

                                            </td>

                                            <td>
                                                {{ $registration->created_at?->translatedFormat('d F Y') }}

                                                <div class="text-secondary small">
                                                    {{ $registration->created_at?->format('H:i') }} WIB
                                                </div>
                                            </td>

                                            <td class="checkin-status-cell">

                                                @if($registration->status === 'attended')

                                                    <span class="badge bg-success-lt">
                                                        <i class="ti ti-shield-check me-1"></i>
                                                        Sudah Check-in
                                                    </span>

                                                @else

                                                    <span class="badge bg-secondary-lt">
                                                        Belum Check-in
                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    @else

                        <div class="event-gallery-empty">

                            <i class="ti ti-users-off"></i>

                            <div class="fw-semibold mt-2">
                                Belum ada peserta
                            </div>

                            <div class="text-secondary small">
                                Belum ada pendaftaran peserta untuk event ini.
                            </div>

                        </div>

                    @endif

                </div>

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
    @hasanyrole(['Tim', 'Super-Admin'])

        <div class="modal modal-blur fade" id="checkinModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-scan me-2"></i>
                            Check-in Peserta
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Kode Tiket</label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control"
                                       id="checkinTicketInput"
                                       placeholder="Contoh: TKT-9F3K2A"
                                       autocomplete="off"
                                       autofocus>
                                <button type="button" class="btn btn-outline-secondary" id="checkinSearchBtn">
                                    <i class="ti ti-search me-1"></i>
                                    Cari
                                </button>
                            </div>
                            <div class="form-text">
                                Masukkan atau scan kode tiket, lalu tekan Enter atau klik Cari untuk menampilkan data peserta.
                            </div>
                        </div>

                        {{-- Kartu preview peserta hasil pencarian --}}
                        <div id="checkinParticipantCard" class="card card-sm border d-none">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="avatar me-3">
                                        <i class="ti ti-user"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold" id="checkinParticipantName">-</div>
                                        <div class="text-secondary small" id="checkinParticipantEmail">-</div>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge" id="checkinParticipantStatusBadge">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="checkinResultBox" class="d-none mt-3">

                            <div class="alert" id="checkinResultAlert" role="alert">
                                <div class="d-flex">
                                    <i class="ti me-2" id="checkinResultIcon"></i>
                                    <div>
                                        <div class="fw-semibold" id="checkinResultTitle"></div>
                                        <div class="text-secondary small" id="checkinResultMessage"></div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="button" class="btn btn-success" id="checkinSubmitBtn" disabled>
                            <i class="ti ti-check me-1"></i>
                            Check-in
                        </button>
                    </div>

                </div>
            </div>
        </div>

        @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const modalEl = document.getElementById('checkinModal');
                const ticketInput = document.getElementById('checkinTicketInput');
                const searchBtn = document.getElementById('checkinSearchBtn');
                const submitBtn = document.getElementById('checkinSubmitBtn');

                const participantCard = document.getElementById('checkinParticipantCard');
                const participantName = document.getElementById('checkinParticipantName');
                const participantEmail = document.getElementById('checkinParticipantEmail');
                const participantStatusBadge = document.getElementById('checkinParticipantStatusBadge');

                const resultBox = document.getElementById('checkinResultBox');
                const resultAlert = document.getElementById('checkinResultAlert');
                const resultIcon = document.getElementById('checkinResultIcon');
                const resultTitle = document.getElementById('checkinResultTitle');
                const resultMessage = document.getElementById('checkinResultMessage');

                let foundTicketCode = null;
                let foundEligible = false;

                function resetModalState() {
                    ticketInput.value = '';
                    participantCard.classList.add('d-none');
                    resultBox.classList.add('d-none');
                    submitBtn.disabled = true;
                    foundTicketCode = null;
                    foundEligible = false;
                }

                function hideResult() {
                    resultBox.classList.add('d-none');
                }

                function showResult(success, title, message) {
                    resultBox.classList.remove('d-none');
                    resultAlert.className = 'alert ' + (success ? 'alert-success' : 'alert-danger');
                    resultIcon.className = 'ti me-2 ' + (success ? 'ti-circle-check' : 'ti-alert-circle');
                    resultTitle.textContent = title;
                    resultMessage.textContent = message;
                }

                function showParticipantCard(participant) {
                    participantCard.classList.remove('d-none');
                    participantName.textContent = participant.name ?? '-';
                    participantEmail.textContent = participant.email ?? '';

                    const statusMap = {
                        attended: { label: 'Sudah Check-in', class: 'bg-success-lt' },
                        paid: { label: 'Paid', class: 'bg-primary-lt' },
                        confirmed: { label: 'Confirmed', class: 'bg-primary-lt' },
                        pending: { label: 'Pending', class: 'bg-warning-lt' },
                        cancelled: { label: 'Cancelled', class: 'bg-danger-lt' },
                    };
                    const info = statusMap[participant.status] ?? { label: participant.status ?? '-', class: 'bg-secondary-lt' };

                    participantStatusBadge.textContent = info.label;
                    participantStatusBadge.className = 'badge ' + info.class;
                }

                function updateRowStatus(ticketCode) {
                    const row = document.querySelector('tr[data-ticket-code="' + CSS.escape(ticketCode) + '"]');
                    if (!row) return;

                    const cell = row.querySelector('.checkin-status-cell');
                    if (!cell) return;

                    cell.innerHTML = '<span class="badge bg-success-lt"><i class="ti ti-shield-check me-1"></i> Sudah Check-in</span>';
                }

                async function searchTicket() {
                    const ticketCode = ticketInput.value.trim();

                    hideResult();
                    participantCard.classList.add('d-none');
                    submitBtn.disabled = true;
                    foundTicketCode = null;
                    foundEligible = false;

                    if (!ticketCode) {
                        showResult(false, 'Kode tiket kosong', 'Masukkan kode tiket terlebih dahulu.');
                        return;
                    }

                    searchBtn.disabled = true;

                    try {
                        const response = await fetch('{{ route("events.participants.lookup", $event->id) }}?ticket_code=' + encodeURIComponent(ticketCode), {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            showParticipantCard(data.participant);
                            foundTicketCode = ticketCode;

                            if (data.participant.status === 'attended') {
                                showResult(false, 'Sudah Check-in', 'Peserta ini sudah check-in sebelumnya.');
                                foundEligible = false;
                            } else if (['paid', 'confirmed'].includes(data.participant.status)) {
                                foundEligible = true;
                            } else {
                                showResult(false, 'Belum Bisa Check-in', 'Peserta belum menyelesaikan pembayaran/konfirmasi.');
                                foundEligible = false;
                            }
                        } else {
                            showResult(false, 'Tidak Ditemukan', data.message ?? 'Kode tiket tidak ditemukan untuk event ini.');
                        }

                    } catch (error) {
                        showResult(false, 'Terjadi Kesalahan', 'Gagal menghubungi server. Coba lagi.');
                    } finally {
                        searchBtn.disabled = false;
                        submitBtn.disabled = !foundEligible;
                    }
                }

                async function submitCheckin() {
                    if (!foundTicketCode || !foundEligible) return;

                    submitBtn.disabled = true;

                    try {
                        const response = await fetch('{{ route("events.participants.checkinScan", $event->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({ ticket_code: foundTicketCode }),
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            showResult(true, 'Check-in Berhasil', data.message ?? (data.participant?.name ?? 'Peserta') + ' berhasil di-check-in.');
                            updateRowStatus(foundTicketCode);

                            if (data.participant) {
                                showParticipantCard(data.participant);
                            }

                            foundEligible = false;
                        } else {
                            showResult(false, 'Check-in Gagal', data.message ?? 'Kode tiket tidak valid atau sudah check-in.');
                        }

                    } catch (error) {
                        showResult(false, 'Terjadi Kesalahan', 'Gagal menghubungi server. Coba lagi.');
                    } finally {
                        submitBtn.disabled = true;
                    }
                }

                searchBtn.addEventListener('click', searchTicket);
                submitBtn.addEventListener('click', submitCheckin);

                ticketInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        searchTicket();
                    }
                });

                if (modalEl) {
                    modalEl.addEventListener('shown.bs.modal', function () {
                        resetModalState();
                        ticketInput.focus();
                    });
                }
            });
        </script>
        @endpush

    @endhasanyrole
</div>

@endsection