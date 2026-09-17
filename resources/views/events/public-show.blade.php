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

/* ============ TWO COLUMN LAYOUT ============ */

.event-detail-grid{
    display:grid;
    grid-template-columns:1.6fr 1fr;
    gap:32px;
    align-items:start;
}

.event-detail-main{
    min-width:0;
}

.event-detail-sidebar{
    position:sticky;
    top:100px;
    display:flex;
    flex-direction:column;
    gap:20px;
}

@media (max-width:992px){
    .event-detail-grid{
        grid-template-columns:1fr;
    }

    .event-detail-sidebar{
        position:static;
        order:-1;
    }
}

/* ============ TABS ============ */

.event-detail-tabs{
    display:flex;
    gap:8px;
    border-bottom:1px solid #eee;
    margin-bottom:28px;
    overflow-x:auto;
}

.event-tab-btn{
    appearance:none;
    background:none;
    border:none;
    padding:12px 4px;
    margin-right:20px;
    font-size:14px;
    font-weight:600;
    color:#888;
    white-space:nowrap;
    cursor:pointer;
    border-bottom:2px solid transparent;
    transition:.2s;
}

.event-tab-btn:hover{
    color:#b7965b;
}

.event-tab-btn.active{
    color:#111;
    border-bottom-color:#b7965b;
}

.event-tab-panel{
    display:none;
}

.event-tab-panel.active{
    display:block;
}

/* ============ SIDEBAR CARDS ============ */

.event-sidebar-card{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
}

.event-sidebar-card h3{
    font-size:15px;
    font-weight:700;
    color:#111;
    margin-bottom:16px;
    display:flex;
    align-items:center;
    gap:8px;
}

.event-sidebar-row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:13px;
    margin-bottom:12px;
}

.event-sidebar-row:last-child{
    margin-bottom:0;
}

.event-sidebar-row .label{
    color:#888;
}

.event-sidebar-row .value{
    font-weight:600;
    color:#222;
    text-align:right;
}

.event-sidebar-badge{
    display:inline-block;
    padding:3px 10px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
}

.event-sidebar-badge.free{ background:#ebfbee; color:#2f9e44; }
.event-sidebar-badge.paid{ background:#fff9db; color:#e8590c; }

.event-sidebar-cta{
    display:flex;
    flex-direction:column;
    gap:10px;
}

/* ============ DESCRIPTION / GALLERY / VIDEO ============ */

.event-detail-description{
    font-size:15px;
    line-height:1.8;
    color:#333;
    margin-bottom:36px;
}

.event-detail-section-title{
    font-size:17px;
    font-weight:700;
    color:#111;
    margin-bottom:16px;
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

.event-detail-video-scroll{
    display:flex;
    gap:16px;
    overflow-x:auto;
    overflow-y:hidden;
    scroll-snap-type:x mandatory;
    padding-bottom:8px;
    -webkit-overflow-scrolling:touch;
    margin-bottom:40px;
}

.event-detail-video-scroll::-webkit-scrollbar{
    height:6px;
}

.event-detail-video-scroll::-webkit-scrollbar-thumb{
    background:rgba(0,0,0,.15);
    border-radius:999px;
}

.event-detail-video-item{
    flex:0 0 auto;
    width:320px;
    scroll-snap-align:start;
}

.event-detail-video{
    position:relative;
    width:100%;
    aspect-ratio:16/9;
    margin-bottom:8px;
    border-radius:16px;
    overflow:hidden;
}

.event-detail-video iframe{
    width:100%;
    height:100%;
    border:0;
    display:block;
}

.event-detail-video-title{
    font-size:13px;
    font-weight:600;
    color:#333;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

/* ============ FAQ ACCORDION ============ */

.event-faq-item{
    border-bottom:1px solid #eee;
}

.event-faq-item:first-child{
    border-top:1px solid #eee;
}

.event-faq-question{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    background:none;
    border:none;
    text-align:left;
    padding:16px 0;
    font-size:14px;
    font-weight:600;
    color:#111;
    cursor:pointer;
}

.event-faq-question i{
    transition:transform .2s;
    color:#b7965b;
    flex-shrink:0;
}

.event-faq-item.open .event-faq-question i{
    transform:rotate(180deg);
}

.event-faq-answer{
    display:none;
    padding:0 0 16px;
    font-size:14px;
    line-height:1.7;
    color:#555;
}

.event-faq-item.open .event-faq-answer{
    display:block;
}

/* ============ RUNDOWN TIMELINE ============ */

.event-timeline-date{
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#888;
    margin:24px 0 12px;
}

.event-timeline-date:first-child{
    margin-top:0;
}

.event-timeline-item{
    display:flex;
    gap:14px;
    margin-bottom:20px;
}

.event-timeline-icon{
    width:32px;
    height:32px;
    flex-shrink:0;
    border-radius:50%;
    background:#f5eee0;
    color:#b7965b;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:14px;
}

.event-timeline-time{
    font-size:12px;
    color:#888;
    margin-bottom:2px;
}

.event-timeline-activity{
    font-size:14px;
    font-weight:700;
    color:#111;
    margin-bottom:4px;
}

.event-timeline-extra{
    display:flex;
    flex-wrap:wrap;
    gap:14px;
    font-size:12px;
    color:#777;
}

.event-timeline-extra span{
    display:flex;
    align-items:center;
    gap:4px;
}

/* ============ SPONSORSHIP ============ */

.event-sponsor-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.event-sponsor-card{
    border:1px solid #eee;
    border-radius:16px;
    padding:24px;
}

.event-sponsor-icon{
    width:48px;
    height:48px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-bottom:16px;
}

.event-sponsor-icon.wa{ background:#ebfbee; color:#2f9e44; }
.event-sponsor-icon.qris{ background:#eef3ff; color:#1971c2; }

.event-sponsor-card h4{
    font-size:15px;
    font-weight:700;
    color:#111;
    margin-bottom:6px;
}

.event-sponsor-card p{
    font-size:13px;
    color:#777;
    margin-bottom:16px;
}

.btn-wa-sponsor{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#2f9e44;
    color:#fff;
    font-weight:600;
    font-size:13px;
    padding:10px 20px;
    border-radius:10px;
    text-decoration:none;
}

.btn-wa-sponsor:hover{
    background:#268a3a;
    color:#fff;
}

.event-empty-state{
    text-align:center;
    padding:40px 20px;
    color:#999;
}

.event-empty-state i{
    font-size:32px;
    margin-bottom:10px;
    display:block;
}

/* ============ SHARED ============ */

.btn-daftar-event{
    display:inline-flex;
    align-items:center;
    justify-content:center;
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
    text-align:center;
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

    .event-sponsor-grid{
        grid-template-columns:1fr;
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
        </div>
    </div>
</section>

<section class="event-detail-body">
    <div class="container">
        <div class="event-detail-grid">

            {{-- ================= MAIN COLUMN ================= --}}
            <div class="event-detail-main">

                {{-- Tab nav --}}
                <div class="event-detail-tabs" role="tablist">
                    <button type="button" class="event-tab-btn active" data-tab-target="tab-detail">
                        Detail Event
                    </button>
                    <button type="button" class="event-tab-btn" data-tab-target="tab-rundown">
                        Rundown
                    </button>
                    <button type="button" class="event-tab-btn" data-tab-target="tab-sponsorship">
                        Sponsorship
                    </button>
                </div>

                {{-- ===== TAB: DETAIL ===== --}}
                <div class="event-tab-panel active" id="tab-detail">

                    <div class="event-detail-description">
                        {!! nl2br(e($event->description)) !!}
                    </div>

                    @if ($event->galleries->isNotEmpty())
                        <div class="event-detail-section-title">Galeri Event</div>

                        <div class="event-detail-gallery">
                            @foreach ($event->galleries as $gallery)
                                <img src="{{ asset('storage/' . $gallery->image) }}" alt="{{ $event->name }}">
                            @endforeach
                        </div>
                    @endif

                    @if ($event->youtubeLinks->isNotEmpty())
                        <div class="event-detail-section-title">Galeri Video</div>

                        <div class="event-detail-video-scroll">
                            @foreach ($event->youtubeLinks as $link)
                                @if ($link->embed_url)
                                    <div class="event-detail-video-item">
                                        <div class="event-detail-video">
                                            <iframe src="{{ $link->embed_url }}"
                                                    title="{{ $link->title ?? $event->name }}"
                                                    loading="lazy"
                                                    allowfullscreen></iframe>
                                        </div>

                                        @if ($link->title)
                                            <div class="event-detail-video-title">
                                                {{ $link->title }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="event-detail-section-title">FAQ</div>

                    @if ($event->faqs && $event->faqs->isNotEmpty())
                        <div class="event-faq-accordion">
                            @foreach ($event->faqs as $index => $faq)
                                <div class="event-faq-item {{ $index === 0 ? 'open' : '' }}">
                                    <button type="button" class="event-faq-question">
                                        <span>{{ $faq->question }}</span>
                                        <i class="ti ti-chevron-down"></i>
                                    </button>

                                    <div class="event-faq-answer">
                                        {!! nl2br(e($faq->answer)) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="event-empty-state">
                            <i class="ti ti-help-circle-off"></i>
                            FAQ untuk event ini belum ditambahkan.
                        </div>
                    @endif

                </div>

                {{-- ===== TAB: RUNDOWN ===== --}}
                <div class="event-tab-panel" id="tab-rundown">

                    @if ($event->rundowns && $event->rundowns->isNotEmpty())

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

                        @foreach ($groupedRundowns as $date => $items)

                            @if ($groupedRundowns->count() > 1)
                                <div class="event-timeline-date">
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                                </div>
                            @endif

                            @foreach ($items as $rundown)
                                <div class="event-timeline-item">
                                    <div class="event-timeline-icon">
                                        <i class="ti ti-clock"></i>
                                    </div>

                                    <div>
                                        <div class="event-timeline-time">
                                            {{ \Carbon\Carbon::parse($rundown->start_time)->format('H:i') }}
                                            @if ($rundown->end_time)
                                                - {{ \Carbon\Carbon::parse($rundown->end_time)->format('H:i') }}
                                            @endif
                                            WIB
                                        </div>

                                        <div class="event-timeline-activity">
                                            {{ $rundown->activity }}
                                        </div>

                                        <div class="event-timeline-extra">
                                            @if ($rundown->speaker && $rundown->speaker !== '-')
                                                <span>
                                                    <i class="ti ti-microphone-2"></i>
                                                    {{ $rundown->speaker }}
                                                </span>
                                            @endif

                                            @if ($rundown->location)
                                                <span>
                                                    <i class="ti ti-map-pin"></i>
                                                    {{ $rundown->location }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        @endforeach

                    @else
                        <div class="event-empty-state">
                            <i class="ti ti-list-details"></i>
                            Rundown acara belum ditambahkan.
                        </div>
                    @endif

                </div>

                {{-- ===== TAB: SPONSORSHIP ===== --}}
                <div class="event-tab-panel" id="tab-sponsorship">

                    <div class="event-sponsor-grid">

                        {{-- WhatsApp Admin --}}
                        <div class="event-sponsor-card">
                            <div class="event-sponsor-icon wa">
                                <i class="ti ti-brand-whatsapp"></i>
                            </div>

                            <h4>Hubungi WA Admin</h4>
                            <p>Hubungi admin untuk informasi sponsorship atau dukungan acara.</p>

                            @if ($event->sponsorship_whatsapp)

                                @php
                                    $whatsappNumber = preg_replace('/[^0-9]/', '', $event->sponsorship_whatsapp);

                                    if (str_starts_with($whatsappNumber, '0')) {
                                        $whatsappNumber = '62' . substr($whatsappNumber, 1);
                                    }
                                @endphp

                                <a href="https://wa.me/{{ $whatsappNumber }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn-wa-sponsor">
                                    <i class="ti ti-brand-whatsapp"></i>
                                    Hubungi Admin
                                </a>

                            @else
                                <div class="event-empty-state">
                                    Kontak WhatsApp belum tersedia.
                                </div>
                            @endif
                        </div>

                        {{-- QRIS --}}
                        <div class="event-sponsor-card">
                            <div class="event-sponsor-icon qris">
                                <i class="ti ti-qrcode"></i>
                            </div>

                            <h4>QRIS</h4>
                            <p>Dukungan acara melalui QRIS.</p>

                            @if ($event->sponsorship_qris)
                                <div class="text-center">
                                    <img src="{{ Storage::url($event->sponsorship_qris) }}"
                                         alt="QRIS {{ $event->name }}"
                                         style="max-width:220px; width:100%; border:1px solid #eee; border-radius:12px; padding:8px;">
                                </div>
                            @else
                                <div class="event-empty-state">
                                    QRIS belum tersedia.
                                </div>
                            @endif
                        </div>

                    </div>

                </div>

            </div>

            {{-- ================= SIDEBAR ================= --}}
            <aside class="event-detail-sidebar">

                <div class="event-sidebar-card event-sidebar-cta">
                    @if (!$event->is_full && $event->is_registration_open)
                        <a href="{{ route('register') }}" class="btn-daftar-event">
                            <i class="ti ti-ticket"></i>
                            Daftar Sekarang
                        </a>
                    @elseif ($event->is_full)
                        <span class="badge-sold-out">Kuota Penuh</span>
                    @else
                        <span class="badge-closed">Pendaftaran Belum/Sudah Ditutup</span>
                    @endif
                </div>

                <div class="event-sidebar-card">
                    <h3><i class="ti ti-info-circle"></i> Info Event</h3>

                    <div class="event-sidebar-row">
                        <span class="label">Jenis Event</span>
                        <span class="value">
                            @if ($event->event_type === 'free')
                                <span class="event-sidebar-badge free">Gratis</span>
                            @else
                                <span class="event-sidebar-badge paid">Berbayar</span>
                            @endif
                        </span>
                    </div>

                    @if ($event->event_type === 'paid')
                        <div class="event-sidebar-row">
                            <span class="label">Harga Tiket</span>
                            <span class="value">Rp{{ number_format($event->price, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="event-sidebar-row">
                        <span class="label">Kuota</span>
                        <span class="value">{{ $event->quota ? $event->quota . ' Peserta' : 'Tidak terbatas' }}</span>
                    </div>
                </div>

                <div class="event-sidebar-card">
                    <h3><i class="ti ti-calendar-event"></i> Registrasi</h3>

                    <div class="event-sidebar-row">
                        <span class="label">Dibuka</span>
                        <span class="value">
                            {{ $event->registration_open ? $event->registration_open->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>

                    <div class="event-sidebar-row">
                        <span class="label">Ditutup</span>
                        <span class="value">
                            {{ $event->registration_close ? $event->registration_close->translatedFormat('d F Y') : '-' }}
                        </span>
                    </div>
                </div>

                <div class="event-sidebar-card">
                    <h3><i class="ti ti-clock"></i> Jadwal Event</h3>

                    <div class="event-sidebar-row">
                        <span class="label">Mulai</span>
                        <span class="value">
                            {{ $event->start_at->translatedFormat('d F Y') }}<br>
                            {{ $event->start_at->format('H:i') }} WIB
                        </span>
                    </div>

                    <div class="event-sidebar-row">
                        <span class="label">Selesai</span>
                        <span class="value">
                            {{ $event->end_at->translatedFormat('d F Y') }}<br>
                            {{ $event->end_at->format('H:i') }} WIB
                        </span>
                    </div>
                </div>

            </aside>

        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---- Tabs ----
    const tabButtons = document.querySelectorAll('.event-tab-btn');
    const tabPanels = document.querySelectorAll('.event-tab-panel');

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.dataset.tabTarget;

            tabButtons.forEach(function (b) { b.classList.remove('active'); });
            tabPanels.forEach(function (p) { p.classList.remove('active'); });

            btn.classList.add('active');
            document.getElementById(targetId)?.classList.add('active');
        });
    });

    // ---- FAQ accordion ----
    document.querySelectorAll('.event-faq-question').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.event-faq-item')?.classList.toggle('open');
        });
    });

});
</script>
@endsection
