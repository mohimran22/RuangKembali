@extends('layouts.website')

@section('content')
<section class="event-detail-hero">
    <div class="container">
        @if ($event->thumbnail)
            <img src="{{ asset('storage/' . $event->thumbnail) }}" alt="{{ $event->name }}" class="event-detail-thumb">
        @endif

        @if ($event->category)
            <span class="event-detail-category">{{ $event->category->name }}</span>
        @endif

        <h1>{{ $event->name }}</h1>

        <div class="event-detail-meta">
            <span>
                <i class="ti ti-calendar"></i>
                {{ $event->start_at?->translatedFormat('d M Y') }}
                @if ($event->end_at && !$event->start_at->isSameDay($event->end_at))
                    - {{ $event->end_at->translatedFormat('d M Y') }}
                @endif
            </span>

            @if ($event->location)
                <span>
                    <i class="ti ti-map-pin"></i>
                    {{ $event->location }}
                </span>
            @endif

            <span class="event-detail-status status-{{ Str::slug($event->status_label) }}">
                {{ $event->status_label }}
            </span>
        </div>
    </div>
</section>

<section class="event-detail-body">
    <div class="container">
        <div class="event-detail-description">
            {!! nl2br(e($event->description)) !!}
        </div>

        @if ($event->galleries->isNotEmpty())
            <div class="event-detail-gallery">
                @foreach ($event->galleries as $gallery)
                    <img src="{{ asset('storage/' . $gallery->image) }}" alt="{{ $event->name }}">
                @endforeach
            </div>
        @endif

        @if ($event->youtube_embed_url)
            <div class="event-detail-video">
                <iframe src="{{ $event->youtube_embed_url }}" allowfullscreen></iframe>
            </div>
        @endif

        @if (!$event->is_full && $event->is_registration_open)
            <a href="{{ route('register') }}" class="btn-daftar-event">Daftar Sekarang</a>
        @elseif ($event->is_full)
            <span class="badge-sold-out">Kuota Penuh</span>
        @else
            <span class="badge-closed">Pendaftaran Belum/Sudah Ditutup</span>
        @endif
    </div>
</section>
@endsection