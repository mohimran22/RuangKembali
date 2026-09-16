<header class="website-header">
    <div class="website-navbar">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="ti ti-menu-2"></i>
        </button>
        <a href="/" class="website-logo">
            <img src="{{ asset('images/logo-landscape.png') }}" alt="Ruang Kembali">
        </a>
        <nav class="website-menu" id="websiteMenu">
            <a href="/">HOME</a>
            <a href="#">ARTIKEL</a>
            {{-- <a href="#">PRODUK</a> --}}
            <div class="menu-dropdown">
                <button type="button" class="menu-link">
                    EVENT
                    <i class="ti ti-chevron-down"></i>
                </button>
                <div class="mega-menu">
                    @forelse ($eventCategories as $category)
                        <div class="mega-menu-item">
                            <a href="#" class="mega-menu-category">
                                {{ $category->name }}
                                <i class="ti ti-chevron-right"></i>
                            </a>
                            <div class="mega-submenu">
                                @forelse ($category->events as $event)
                                    <a href="{{ route('events.show', $event->event_code) }}">
                                        {{ $event->name }}
                                    </a>
                                @empty
                                    <span class="mega-submenu-empty">Belum ada event</span>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <span class="mega-menu-empty">Belum ada event tersedia</span>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('register') }}">BERGABUNG</a>
            <a href="#">TENTANG KAMI</a>
        </nav>
        <div class="website-icons">
            <a href="#"><i class="ti ti-search"></i></a>
            <a href="#"><i class="ti ti-shopping-bag"></i></a>
            <div class="user-dropdown" id="userDropdown">
                <button class="user-btn" id="userBtn">
                    <i class="ti ti-user"></i>
                </button>
                <div class="user-menu">
                    @guest
                        <a href="{{ route('login') }}">
                            <i class="ti ti-login"></i>
                            Masuk
                        </a>

                        <a href="{{ route('register') }}">
                            <i class="ti ti-user-plus"></i>
                            Daftar
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}">
                            <i class="ti ti-layout-dashboard"></i>
                            Dashboard
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">
                                <i class="ti ti-logout"></i>
                                Logout
                            </button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-overlay" id="mobileOverlay"></div>
</header>
<style>
.website-header{
    position:fixed;
    top:20px;
    left:0;
    width:100%;
    z-index:9999;
}
.website-navbar{

    width:min(92%,1500px);

    margin:auto;

    background:#fff;

    border-radius:22px;

    box-shadow:0 15px 40px rgba(0,0,0,.15);

    height:72px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    padding:0 32px;
}
.website-logo img{
    height:40px;
    display:block;
}
.website-menu{
    display:flex;
    align-items:center;
    gap:38px;
}
.website-menu>a,
.menu-link{
    background:none;
    border:none;
    color:#111;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    letter-spacing:.4px;

    cursor:pointer;

    display:flex;

    align-items:center;

    gap:5px;

    transition:.25s;

}

.website-menu>a:hover,
.menu-link:hover{

    color:#b7965b;

}
.website-icons{
    display:flex;
    align-items:center;
    gap:22px;
}

.website-icons a,
.user-btn{
    background:none;
    border:none;
    color:#111;
    font-size:28px;
    cursor:pointer;
    text-decoration:none;
}

.user-dropdown{
    position:relative;
}
.mobile-menu-btn{
    display:none;
}
.user-menu{
    position:absolute;
    top:45px;
    right:0;

    width:190px;

    background:#fff;
    border-radius:12px;

    box-shadow:0 15px 35px rgba(0,0,0,.15);

    display:none;

    overflow:hidden;

    z-index:99;
}

.user-menu a,
.user-menu button{

    width:100%;

    padding:14px 18px;

    display:flex;

    align-items:center;

    gap:10px;

    background:none;

    border:none;

    text-align:left;

    color:#111;

    text-decoration:none;

    cursor:pointer;

    font-size:15px;
}

.user-menu a:hover,
.user-menu button:hover{

    background:#f5f5f5;

}

.user-dropdown.open .user-menu{

    display:block;

}
.menu-dropdown{
    position:relative;
}
.mega-menu{
    position:absolute;
    top:55px;

    left:50%;

    transform:translateX(-50%);

    width:360px;

    background:#DCCBA8;

    display:none;

    box-shadow:0 15px 35px rgba(0,0,0,.18);

}

.menu-dropdown.open .mega-menu{

    display:block;

}
.mega-menu a{

    display:block;

    padding:18px 28px;

    color:#111;

    text-decoration:none;

    font-weight:600;

    border-bottom:1px solid rgba(0,0,0,.15);

}

.mega-menu a:last-child{

    border-bottom:none;

}

.mega-menu a:hover{

    background:#c3bbb0;

}
.mega-menu-item{
    position:relative;
}

.mega-menu-category{
    display:flex !important;
    justify-content:space-between;
    align-items:center;
}

.mega-menu-category i{
    font-size:16px;
    transition:.2s;
}

.mega-submenu{
    display:none;
    position:absolute;
    top:0;
    left:100%;
    width:280px;
    background:#DCCBA8;
    box-shadow:0 15px 35px rgba(0,0,0,.18);
}

.mega-menu-item.open .mega-submenu{
    display:block;
}

.mega-menu-item.open > .mega-menu-category i{
    transform:rotate(90deg);
}

.mega-submenu a{
    border-bottom:1px solid rgba(0,0,0,.15);
}

.mega-submenu a:last-child{
    border-bottom:none;
}

.mega-menu-empty,
.mega-submenu-empty{
    display:block;
    padding:18px 28px;
    color:#555;
    font-size:13px;
    font-style:italic;
}
@media (min-width: 992px) and (max-width: 1200px) {

    .website-navbar{
        padding:0 20px;
    }

    .website-menu{
        gap:18px;
    }

    .website-menu a,
    .menu-link{
        font-size:13px;
    }

    .website-icons{
        gap:10px;
    }

    .website-icons a,
    .user-btn{
        font-size:22px;
    }
}
@media (max-width:768px){

    .website-navbar{

        display:grid;
        grid-template-columns:44px 1fr 44px;

        align-items:center;

        height:68px;

        padding:0 16px;
    }

    .website-logo{

        justify-self:center;
    }

    .website-logo img{

        height:32px;
    }

    .mobile-menu-btn{

        display:flex;
        align-items:center;
        justify-content:center;

        width:44px;
        height:44px;

        border:none;
        background:none;

        font-size:28px;

        cursor:pointer;
    }

    .website-icons{

        justify-self:end;
    }

    .website-icons>a{

        display:none;
    }
    .website-menu{
        position:fixed;
        top:0;
        left:-340px;

        width:340px;
        max-width:85%;
        align-items:stretch;
        height:100vh;

        background:#fff;

        padding:90px 24px 30px;

        display:flex;
        flex-direction:column;

        overflow-y:auto;

        transition:left .35s ease;

        box-shadow:8px 0 30px rgba(0,0,0,.15);

        z-index:10001;
    }

    .website-menu.active{
        left:0;
    }
    .website-menu a,
    .menu-link{
        display:flex;
        justify-content:space-between;

        width:100%;
        padding:16px 0;

        font-size:14px;
        font-weight:600;
        text-align:left;

        border-bottom:1px solid #f1f1f1;
    }

    .menu-link{
        background:none;
        border:none;
    }
    .mobile-overlay{

    position:fixed;

    inset:0;

    background:rgba(0,0,0,.45);

    opacity:0;
    visibility:hidden;

    transition:.3s;

    z-index:10000;
}

.mobile-overlay.show{

    opacity:1;
    visibility:visible;
}
.mega-menu{
    display:none;
    position:static;
    left:auto;
    top:auto;
    transform:none;
    margin:10px 0 12px 16px;
    width:calc(100% - 16px);
    padding:8px 0;
    background:#f8f8f8;

    border-radius:0 10px 10px 0;

    padding:8px 0;
    overflow:hidden;

    box-shadow:none;
}

.menu-dropdown.open .mega-menu{
    display:block;
}

.mega-menu a{
    display:block;

    padding:12px 18px;

    margin:0;

    font-size:13px;
    color:#555;

    border:none;
}
.mega-submenu{
    position:static;
    width:100%;
    box-shadow:none;
    margin-left:12px;
}

.mega-submenu a{
    font-size:12px;
    padding:10px 18px;
}
}
</style>
<script>
const menu = document.getElementById("websiteMenu");
const btn = document.getElementById("mobileMenuBtn");
const overlay = document.getElementById("mobileOverlay");
btn.onclick = () => {

    menu.classList.add("active");
    overlay.classList.add("show");

};

overlay.onclick = () => {

    menu.classList.remove("active");
    overlay.classList.remove("show");

};
document.querySelectorAll(".menu-link").forEach(button => {
    button.addEventListener("click", function(e){
        e.preventDefault();
        const dropdown = this.closest(".menu-dropdown");
        const isOpen = dropdown.classList.contains("open");

        dropdown.classList.toggle("open");

        // kalau baru ditutup, reset semua submenu kategori di dalamnya
        if (isOpen) {
            dropdown.querySelectorAll(".mega-menu-item.open")
                .forEach(item => item.classList.remove("open"));
        }
    });
});
document.querySelectorAll(".mega-menu-category").forEach(button => {
    button.addEventListener("click", function(e){
        e.preventDefault();
        e.stopPropagation(); // biar gak ikut nutup .menu-dropdown parent

        const currentItem = this.closest(".mega-menu-item");
        const isOpen = currentItem.classList.contains("open");

        // tutup semua kategori lain dulu
        document.querySelectorAll(".mega-menu-item.open").forEach(item => {
            item.classList.remove("open");
        });

        // toggle kategori yang diklik
        if (!isOpen) {
            currentItem.classList.add("open");
        }
    });
});
const userBtn = document.getElementById('userBtn');
const userDropdown = document.getElementById('userDropdown');

userBtn.addEventListener('click', function(e){
    e.stopPropagation();

    userDropdown.classList.toggle('open');
});

document.addEventListener('click', function(){
    userDropdown.classList.remove('open');
});
window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
        menu.classList.remove('active');
        overlay.classList.remove('show');
        document.querySelectorAll('.menu-dropdown')
            .forEach(item => item.classList.remove('open'));
        document.querySelectorAll('.mega-menu-item')
            .forEach(item => item.classList.remove('open')); // tambahan ini
    }
});
</script>