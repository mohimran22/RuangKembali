@extends('layouts.website')
@section('content')
<style>
.hero{
    position: relative;
    margin-top: 0;
    height: 100vh;
    overflow: hidden;
}
.hero-caption{
    position:absolute;
    left:12%;
    bottom:22%;
    z-index:3;
    color:#fff;
    text-align:left;
    display:block;
    padding:0;
}

.hero-caption h3{
    margin:0;
    font-family: "Montserrat", serif;
    font-size:38px;
    font-weight:400;
    line-height:1.05;
    letter-spacing:-0.5px;
    color:#fff;
    max-width:720px;
}
.carousel,
.carousel-inner,
.carousel-item{
    height:100vh;
}

.carousel-item img{
    width:100%;
    height:100%;
    object-fit:cover;
    object-position:center;
}
.carousel-control-prev,
.carousel-control-next{

    width:90px;

    z-index:4;

}
.carousel-indicators{

    bottom:40px;

    z-index:4;

}

.carousel-indicators button{

    width:120px !important;

    height:4px !important;

    border-radius:20px;

    margin:0 8px !important;
}
.website-header{
    position: fixed;
    top: 20px;
    left: 0;
    width: 100%;
    z-index:9999;
}
.event-section{
    padding:100px 5% 80px;
    max-width:1500px;
    margin:auto;
}

.event-section-header{
    text-align:center;
    margin-bottom:48px;
}

.event-section-header h2{
    font-size:32px;
    font-weight:700;
    color:#111;
    margin-bottom:10px;
}

.event-section-header p{
    color:#666;
    font-size:15px;
}

.event-grid{
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:28px;
}

.event-card{
    display:flex;
    flex-direction:column;
    background:#fff;
    border-radius:16px;
    overflow:hidden;
    text-decoration:none;
    color:inherit;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    transition:transform .25s, box-shadow .25s;
}

.event-card:hover{
    transform:translateY(-6px);
    box-shadow:0 16px 36px rgba(0,0,0,.14);
}

.event-card-thumb{
    position:relative;
    height:180px;
    background:#f2f2f2;
}

.event-card-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.event-card-thumb-placeholder{
    width:100%;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#bbb;
}

.event-card-badge{
    position:absolute;
    top:12px;
    left:12px;
    padding:5px 12px;
    border-radius:20px;
    font-size:11px;
    font-weight:700;
    color:#fff;
    background:#b7965b;
}

.event-card-badge.status-coming-soon{ background:#8a8a8a; }
.event-card-badge.status-pendaftaran{ background:#2f9e44; }
.event-card-badge.status-sold-out{ background:#c92a2a; }
.event-card-badge.status-sedang-berlangsung{ background:#1971c2; }
.event-card-badge.status-selesai{ background:#868e96; }

.event-card-body{
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:8px;
}

.event-card-category{
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#b7965b;
}

.event-card-title{
    font-size:17px;
    font-weight:700;
    color:#111;
    line-height:1.35;
}

.event-card-meta{
    display:flex;
    flex-direction:column;
    gap:4px;
    font-size:13px;
    color:#666;
    margin:4px 0 10px;
}

.event-card-meta span{
    display:flex;
    align-items:center;
    gap:6px;
}

.event-card-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding-top:12px;
    border-top:1px solid #f0f0f0;
}

.event-card-price{
    font-weight:700;
    color:#111;
    font-size:14px;
}

.event-card-cta{
    display:flex;
    align-items:center;
    gap:4px;
    font-size:13px;
    font-weight:600;
    color:#b7965b;
}

.event-empty{
    grid-column:1 / -1;
    text-align:center;
    color:#888;
    padding:40px 0;
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
.join-section{
    padding:0 5% 100px;
    max-width:1500px;
    margin:auto;
}

.join-card{
    background:linear-gradient(135deg, #DCCBA8, #C9AF7C);
    border-radius:24px;
    padding:70px 40px;
    text-align:center;
    color:#2b2318;
}

.join-card h2{
    font-size:30px;
    font-weight:700;
    margin-bottom:14px;
    color:#1f1a12;
}

.join-card p{
    font-size:15px;
    color:#3d3423;
    max-width:520px;
    margin:0 auto 28px;
    line-height:1.6;
}

.btn-join{
    display:inline-flex;
    align-items:center;
    gap:8px;
    background:#1f1a12;
    color:#fff;
    font-weight:600;
    font-size:14px;
    padding:14px 32px;
    border-radius:12px;
    text-decoration:none;
    transition:.25s;
}

.btn-join:hover{
    background:#332a1d;
    color:#fff;
}

@media (min-width: 992px) and (max-width: 1200px) {
    .hero-content {
        gap: 40px;
    }
    .brand-logo {
        width: 130px;
    }
    .btn-hero {
        min-width: 180px;
    }
    .event-grid{
        grid-template-columns:repeat(2, 1fr);
    }
}

@media (max-width:768px){
    .hero {
        background:
            linear-gradient(
                to bottom,
                rgba(0,0,0,0.65),
                rgba(0,0,0,0.35)
            ),
            url('{{ asset("hero-mobile.jpeg") }}');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: 85% top;
        margin-top:0;
        height:100vh;
        position:relative;
    }
    .hero-caption{
        position: absolute;
        z-index: 10;
        color: #fff;
    }
    .hero-caption h3{
        font-size:28px;
        line-height:1.2;
    }
    .brand-logo {
        width: 120px;
        margin-top: -25px;
    }
    .button-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        gap: 16px;
    }
    .btn-hero {
        width: 100%;
        max-width: 270px;
        min-width: unset;
        padding: 13px 20px;
        font-size: 15px;
        border-radius: 12px;
    }
    .footer-text {
        font-size: 11px;
        bottom: 18px;
        padding: 0 18px;
    }
    .carousel{
        display:none;
    }
    .carousel,
    .carousel-inner,
    .carousel-item{
        height:100%;
    }

    .carousel-item img{
        width:100%;
        height:100%;
        object-fit:cover;
    }
    .event-grid{
        grid-template-columns:1fr;
    }

    .event-section{
        padding:70px 5% 50px;
    }
    .footer-info-grid{
        grid-template-columns:1fr;
    }
    .join-card{
        padding:50px 24px;
    }

    .join-card h2{
        font-size:24px;
    }
}
</style>
<section class="hero">
    <div id="heroSlider" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('images/hero-dekstop.jpeg') }}">
            </div>
            {{-- <div class="carousel-item">
                <img src="{{ asset('images/coba-hero.jpeg') }}">
            </div> --}}
        </div>
        <div class="hero-overlay"></div>

        {{-- <button class="carousel-control-prev"
                data-bs-target="#heroSlider"
                data-bs-slide="prev">

            <span class="carousel-control-prev-icon"></span>

        </button>

        <button class="carousel-control-next"
                data-bs-target="#heroSlider"
                data-bs-slide="next">

            <span class="carousel-control-next-icon"></span>

        </button> --}}

        {{-- <div class="carousel-indicators">

            <button data-bs-target="#heroSlider"
                    data-bs-slide-to="0"
                    class="active"></button>

            <button data-bs-target="#heroSlider"
                    data-bs-slide-to="1"></button>

        </div> --}}

    </div>
            <div class="hero-caption">

            <h3>
                Sebuah Ruang untuk Bertumbuh
                <br>
                Sebuah Ruang untuk Kembali
            </h3>

        </div>
</section>
<section class="event-section">
    <div class="event-section-header">
        <h2>Event Terbaru</h2>
        <p>Ikuti event-event kami yang sedang berlangsung dan akan datang</p>
    </div>

    <div class="event-grid">
        @forelse ($events as $event)
            <a href="{{ route('events.show', $event->event_code) }}" class="event-card">
                <div class="event-card-thumb">
                    @if ($event->thumbnail)
                        <img src="{{ asset('storage/' . $event->thumbnail) }}" alt="{{ $event->name }}">
                    @else
                        <div class="event-card-thumb-placeholder">
                            <i class="ti ti-calendar-event"></i>
                        </div>
                    @endif

                    <span class="event-card-badge status-{{ Str::slug($event->status_label) }}">
                        {{ $event->status_label }}
                    </span>
                </div>

                <div class="event-card-body">
                    @if ($event->category)
                        <span class="event-card-category">{{ $event->category->name }}</span>
                    @endif

                    <h3 class="event-card-title">{{ $event->name }}</h3>

                    <div class="event-card-meta">
                        <span>
                            <i class="ti ti-calendar"></i>
                            {{ $event->start_at?->translatedFormat('d M Y') }}
                        </span>
                        @if ($event->location)
                            <span>
                                <i class="ti ti-map-pin"></i>
                                {{ $event->location }}
                            </span>
                        @endif
                    </div>

                    <div class="event-card-footer">
                        <span class="event-card-price">
                            @if ($event->price > 0)
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            @else
                                Gratis
                            @endif
                        </span>
                        <span class="event-card-cta">
                            Lihat Detail <i class="ti ti-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
        @empty
            <p class="event-empty">Belum ada event tersedia saat ini.</p>
        @endforelse
    </div>
</section>
<section class="join-section">
    <div class="join-card">
        <h2>Bergabung Bersama Kami</h2>
        <p>
            Jadilah bagian dari Ruang Kembali — ikuti kajian, event, dan program
            kebaikan yang membantu kamu terus bertumbuh.
        </p>
        <a href="{{ route('register') }}" class="btn-join">
            Bergabung Sekarang <i class="ti ti-arrow-right"></i>
        </a>
    </div>
</section>
@endsection