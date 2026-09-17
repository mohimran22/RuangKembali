@extends('layouts.website')

@section('content')
<style>
.event-detail-hero{
    position:relative;
    padding:140px 5% 40px;
    max-width:1100px;
    margin:auto;
}

.event-detail-thumb{
    width:100%;
    height: 100%;
    object-fit:cover;
    border-radius:20px;
    margin-bottom:28px;
    display:block;
}

.event-detail-category{
    display:inline-block;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#b7965b;
    margin-bottom:10px;
}

.event-detail-hero h1{
    font-size:32px;
    font-weight:700;
    color:#111;
    line-height:1.25;
    margin-bottom:18px;
}

.event-detail-meta{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:20px;
    font-size:14px;
    color:#555;
}

.event-detail-meta span{
    display:flex;
    align-items:center;
    gap:6px;
}

.event-detail-status{
    padding:5px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
    color:#fff;
    background:#b7965b;
}

.event-detail-status.status-coming-soon{ background:#8a8a8a; }
.event-detail-status.status-pendaftaran{ background:#2f9e44; }
.event-detail-status.status-sold-out{ background:#c92a2a; }
.event-detail-status.status-sedang-berlangsung{ background:#1971c2; }
.event-detail-status.status-selesai{ background:#868e96; }

.event-detail-body{
    max-width:1100px;
    margin:auto;
    padding:0 5% 100px;
}

.event-detail-description{
    font-size:15px;
    line-height:1.8;
    color:#333;
    margin-bottom:40px;
}

.event-detail-gallery{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:14px;
    margin-bottom:40px;
}

.event-detail-gallery img{
    width:100%;
    height:180px;
    object-fit:cover;
    border-radius:12px;
}

.event-detail-video{
    position:relative;
    width:100%;
    max-width:800px;
    aspect-ratio:16/9;
    margin:0 auto 40px;
    border-radius:16px;
    overflow:hidden;
}

.event-detail-video iframe{
    width:100%;
    height:100%;
    border:0;
    display:block;
}

.btn-daftar-event{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#b7965b;
    color:#fff;
    font-weight:600;
    font-size:14px;
    padding:14px 32px;
    border-radius:12px;
    text-decoration:none;
    transition:.25s;
}

.btn-daftar-event:hover{
    background:#a3854f;
    color:#fff;
}

.badge-sold-out,
.badge-closed{
    display:inline-block;
    padding:12px 24px;
    border-radius:12px;
    font-weight:600;
    font-size:14px;
}

.badge-sold-out{
    background:#fdecec;
    color:#c92a2a;
}

.badge-closed{
    background:#f1f1f1;
    color:#666;
}
.website-footer{
    background:#f8f6f2;
    padding-top:60px;
}

.footer-info-section{
    max-width:1500px;
    margin:auto;
    padding:0 5% 60px;
}

.footer-info-header{
    text-align:center;
    margin-bottom:40px;
}

.footer-info-header h2{
    font-size:28px;
    font-weight:700;
    color:#111;
    margin-bottom:8px;
}

.footer-info-header p{
    color:#666;
    font-size:14px;
}

.footer-info-grid{
    display:grid;
    grid-template-columns:1.2fr 1fr;
    gap:28px;
    align-items:start;
}

.footer-location-card,
.footer-contact-card{
    background:#fff;
    border-radius:16px;
    padding:28px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}

.footer-location-card h3,
.footer-contact-card h3{
    display:flex;
    align-items:center;
    gap:8px;
    font-size:18px;
    font-weight:700;
    color:#111;
    margin-bottom:14px;
}
.footer-location-card{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    padding:40px 28px;
}

.footer-brand-logo{
    height:56px;
    margin-bottom:16px;
}

.footer-brand-tagline{
    font-size:14px;
    color:#666;
    max-width:320px;
    line-height:1.5;
}
.footer-location-address{
    font-size:14px;
    color:#333;
    margin-bottom:6px;
}

.footer-location-hours{
    font-size:13px;
    color:#888;
    margin-bottom:16px;
}

.footer-map-btn{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#eef3ff;
    color:#1971c2;
    font-weight:600;
    font-size:13px;
    padding:8px 14px;
    border-radius:8px;
    text-decoration:none;
    margin-bottom:16px;
}

.footer-map-btn:hover{
    background:#dce7fb;
}

.footer-map-embed{
    border-radius:12px;
    overflow:hidden;
}

.footer-map-embed iframe{
    display:block;
}

.footer-contact-list{
    list-style:none;
    padding:0;
    margin:0 0 16px;
    display:flex;
    flex-direction:column;
    gap:14px;
}

.footer-contact-list li{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:14px;
}

.footer-contact-list i{
    font-size:20px;
    color:#c92a2a;
    flex-shrink:0;
}

.footer-contact-list a{
    color:#333;
    text-decoration:none;
}

.footer-contact-list a:hover{
    color:#b7965b;
}

.footer-contact-note{
    font-size:12px;
    color:#999;
    border-top:1px solid #eee;
    padding-top:14px;
}

.footer-contact-note a{
    color:#b7965b;
    font-weight:600;
    text-decoration:none;
}

.footer-bottom{
    text-align:center;
    padding:20px 5%;
    border-top:1px solid #eee;
    font-size:12px;
    color:#888;
}
@media (max-width:768px){
    .event-detail-hero{
        padding:120px 5% 20px;
    }

    .event-detail-hero h1{
        font-size:24px;
    }

    .event-detail-thumb{
        max-height:220px;
    }

    .event-detail-gallery{
        grid-template-columns:repeat(2, 1fr);
    }

    .event-detail-gallery img{
        height:130px;
    }
    .footer-info-grid{
        grid-template-columns:1fr;
    }
}
</style>

<section class="event-detail-hero">
    <div class="container">
        @if ($event->poster)
            <img src="{{ asset('storage/' . $event->poster) }}" alt="{{ $event->name }}" class="event-detail-thumb">
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
            @if (!$event->is_full && $event->is_registration_open)
                <a href="{{ route('register') }}" class="btn-daftar-event">Daftar Sekarang</a>
            @elseif ($event->is_full)
                <span class="badge-sold-out">Kuota Penuh</span>
            @else
                <span class="badge-closed">Pendaftaran Belum/Sudah Ditutup</span>
            @endif
        </div>
    </div>
</section>

<section class="event-detail-body">
    <div class="container">
        <div class="event-detail-description">
            {!! nl2br(e($event->description)) !!}
        </div>
        Galeri Event
        @if ($event->galleries->isNotEmpty())
            <div class="event-detail-gallery">
                @foreach ($event->galleries as $gallery)
                    <img src="{{ asset('storage/' . $gallery->image) }}" alt="{{ $event->name }}">
                @endforeach
            </div>
        @endif
        Galeri Video
        @if ($event->youtube_embed_url)
            <div class="event-detail-video">
                <iframe src="{{ $event->youtube_embed_url }}" allowfullscreen></iframe>
            </div>
        @endif
    </div>
</section>
@endsection