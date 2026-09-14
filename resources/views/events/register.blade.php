@extends('tablar::page')

@section('content')

<div class="container-xl">

    <div class="page-header d-print-none mb-4">

        <div class="row align-items-center">

            <div class="col">

                <h2>
                    Pendaftaran Event
                </h2>

                <div class="text-secondary">
                    Silakan lengkapi proses pendaftaran untuk mengikuti event.
                </div>

            </div>

        </div>

    </div>


    {{-- Alert --}}
    @if(session('success'))

        <div class="alert alert-success">
            <i class="ti ti-circle-check me-2"></i>
            {{ session('success') }}
        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger">
            <i class="ti ti-alert-circle me-2"></i>
            {{ session('error') }}
        </div>

    @endif


    <div class="row g-4">

        {{-- Informasi Event --}}
        <div class="col-lg-5">

            <div class="card">

                @if($event->poster)

                    <img src="{{ Storage::url($event->poster) }}"
                         alt="{{ $event->name }}"
                         class="card-img-top">

                @endif


                <div class="card-body">

                    <div class="text-secondary small mb-2">
                        {{ $event->category?->name }}
                    </div>

                    <h2 class="mb-3">
                        {{ $event->name }}
                    </h2>


                    <div class="mb-3">

                        <div class="text-secondary small">
                            <i class="ti ti-calendar me-1"></i>
                            Jadwal
                        </div>

                        <div class="fw-semibold">
                            {{ $event->start_at->translatedFormat('d F Y') }}
                        </div>

                        <div class="text-secondary">
                            {{ $event->start_at->format('H:i') }}
                            -
                            {{ $event->end_at->format('H:i') }}
                            WIB
                        </div>

                    </div>


                    @if($event->location)

                        <div class="mb-3">

                            <div class="text-secondary small">
                                <i class="ti ti-map-pin me-1"></i>
                                Lokasi
                            </div>

                            <div class="fw-semibold">
                                {{ $event->location }}
                            </div>

                        </div>

                    @endif


                    <div>

                        <div class="text-secondary small">
                            Harga Tiket
                        </div>

                        <div class="fs-2 fw-bold">

                            @if($event->event_type === 'free')
                                Gratis
                            @else
                                Rp {{ number_format($event->price, 0, ',', '.') }}
                            @endif

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Form Pendaftaran --}}
        <div class="col-lg-7">

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        <i class="ti ti-user-plus me-2"></i>
                        Form Pendaftaran
                    </h3>

                </div>


                <div class="card-body">

                    @if($registration)

                        <div class="alert alert-success">

                            <div class="d-flex">

                                <i class="ti ti-circle-check fs-2 me-3"></i>

                                <div>

                                    <h3 class="alert-title">
                                        Kamu sudah terdaftar
                                    </h3>

                                    <div class="text-secondary">
                                        Kamu sudah memiliki pendaftaran
                                        untuk event ini.
                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="card card-sm border">

                            <div class="card-body">

                                <div class="text-secondary small">
                                    Kode Tiket
                                </div>

                                <div class="fs-2 fw-bold">
                                    {{ $registration->ticket_code }}
                                </div>


                                <div class="mt-3">

                                    <div class="text-secondary small">
                                        Status
                                    </div>

                                    <span class="badge bg-success-lt">
                                        {{ ucfirst($registration->status) }}
                                    </span>

                                </div>

                            </div>

                        </div>

                        @else

                        <form action="{{ route('events.register.store', $event->id) }}"
                            method="POST"
                            id="registrationForm">

                            @csrf

                            <div class="mb-4">

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 class="mb-0">Data Peserta</h3>
                                    <div class="text-end">
                                        <div class="text-secondary small">No. Transaksi</div>
                                        <div class="fw-semibold">{{ $transactionCode }}</div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Peserta yang ditambahkan harus sudah memiliki akun terdaftar di sistem.
                                </div>

                                <div id="participantsWrapper">

                                    @unless(auth()->user()->hasRole('Super-Admin'))
                                        {{-- Peserta pertama = akun yang login, tidak bisa dihapus --}}
                                        <div class="card card-sm border mb-3 participant-row" data-user-id="{{ auth()->id() }}">
                                            <div class="card-body d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="text-secondary small fw-semibold">Kamu (Pendaftar)</div>
                                                    <div class="fw-semibold">{{ auth()->user()->fullname ?? auth()->user()->name }}</div>
                                                    <div class="text-secondary small">{{ auth()->user()->email }}</div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="participants[0][user_id]" value="{{ auth()->id() }}">
                                        </div>
                                    @endunless

                                </div>

                                {{-- Search box untuk tambah peserta --}}
                                <div class="position-relative mb-2">

                                    <input type="text"
                                        class="form-control"
                                        id="participantSearchInput"
                                        placeholder="Cari nama atau email peserta terdaftar...">

                                    <div class="list-group position-absolute w-100 bg-white shadow-sm rounded border"
                                        id="participantSearchResults"
                                        style="z-index: 1000; display: none; max-height: 250px; overflow-y: auto; top: 100%;">
                                    </div>

                                </div>

                            </div>

                            {{-- Hidden input transaction_code: selalu ikut ke-submit, terlepas jenis event --}}
                            <input type="hidden" name="transaction_code" value="{{ $transactionCode }}">
                            @if($event->event_type !== 'free')

                                <div class="mb-4">

                                    <h3 class="mb-3">
                                        Ringkasan Pembayaran
                                    </h3>

                                    <div class="card card-sm border mb-3">

                                        <div class="card-body">
                                            {{-- <div class="d-flex justify-content-between mb-2">
                                                <div class="text-secondary">
                                                    No. Transaksi
                                                </div>
                                                <div class="fw-semibold">
                                                    {{ $transactionCode }}
                                                </div>
                                            </div>

                                            <hr class="my-2"> --}}
                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="text-secondary">
                                                    Harga per Peserta
                                                </div>
                                                <div class="fw-semibold">
                                                    Rp {{ number_format($event->price, 0, ',', '.') }}
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="text-secondary">
                                                    Jumlah Peserta
                                                </div>
                                                <div class="fw-semibold" id="participantCountLabel">
                                                    1
                                                </div>
                                            </div>

                                            <hr class="my-2">

                                            <div class="d-flex justify-content-between">
                                                <div class="fw-bold">
                                                    Total Harga
                                                </div>
                                                <div class="fs-3 fw-bold text-primary" id="totalPriceLabel">
                                                    Rp {{ number_format($event->price, 0, ',', '.') }}
                                                </div>
                                            </div>
                                            <input type="hidden" name="total_price" id="totalPriceInput" value="{{ $event->price }}">
                                            {{-- <input type="hidden" name="transaction_code" value="{{ $transactionCode }}"> --}}

                                        </div>

                                    </div>
                                    <div class="mb-3">

                                        <label class="form-label">
                                            Metode Pembayaran
                                        </label>

                                        <div class="form-selectgroup w-100">

                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="radio"
                                                    name="payment_method"
                                                    value="transfer"
                                                    class="form-selectgroup-input"
                                                    required>
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <i class="ti ti-building-bank me-2 fs-2"></i>
                                                    <div>
                                                        <div class="fw-semibold">Transfer Bank</div>
                                                        <div class="text-secondary small">Upload bukti transfer manual</div>
                                                    </div>
                                                </div>
                                            </label>

                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="radio"
                                                    name="payment_method"
                                                    value="gateway"
                                                    class="form-selectgroup-input"
                                                    disabled>
                                                <div class="form-selectgroup-label d-flex align-items-center p-3 opacity-50">
                                                    <i class="ti ti-credit-card me-2 fs-2"></i>
                                                    <div>
                                                        <div class="fw-semibold">
                                                            Payment Gateway
                                                            <span class="badge bg-secondary-lt ms-1">Segera Hadir</span>
                                                        </div>
                                                        <div class="text-secondary small">QRIS, e-wallet, kartu, dll — belum tersedia</div>
                                                    </div>
                                                </div>
                                            </label>

                                        </div>

                                    </div>

                                </div>

                            @endif

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="registrationAgreement" required>
                                    <label class="form-check-label" for="registrationAgreement">
                                        Saya menyatakan bahwa data yang digunakan untuk pendaftaran adalah benar dan bersedia mengikuti ketentuan event.
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>
                                    Daftar Event
                                </button>
                            </div>

                        </form>

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
@push('js')
<script>
(function () {
    const wrapper = document.getElementById('participantsWrapper');
    const searchInput = document.getElementById('participantSearchInput');
    const searchResults = document.getElementById('participantSearchResults');

    const eventId = '{{ $event->id }}';
    const pricePerParticipant = {{ $event->price ?? 0 }};

    let debounceTimer = null;

    function getSelectedUserIds() {
        return Array.from(wrapper.querySelectorAll('.participant-row'))
            .map(row => row.dataset.userId);
    }

    function renumberParticipants() {
        const rows = wrapper.querySelectorAll('.participant-row');

        rows.forEach(function (row, index) {
            const hiddenInput = row.querySelector('input[type="hidden"]');
            hiddenInput.name = `participants[${index}][user_id]`;
        });

        updateTotalPrice(rows.length);
    }

    function updateTotalPrice(count) {
        const total = pricePerParticipant * count;

        const countLabel = document.getElementById('participantCountLabel');
        const totalLabel = document.getElementById('totalPriceLabel');
        const totalInput = document.getElementById('totalPriceInput');

        if (countLabel) countLabel.textContent = count;
        if (totalLabel) totalLabel.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (totalInput) totalInput.value = total;
    }

    function addParticipant(user) {
        if (getSelectedUserIds().includes(String(user.id))) {
            return; // sudah dipilih, jangan duplikat
        }

        const row = document.createElement('div');
        row.className = 'card card-sm border mb-3 participant-row';
        row.dataset.userId = user.id;

        row.innerHTML = `
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${user.name}</div>
                    <div class="text-secondary small">${user.email}</div>
                </div>
                <button type="button" class="btn btn-sm btn-link text-danger removeParticipantBtn">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
            <input type="hidden" name="participants[__index__][user_id]" value="${user.id}">
        `;

        wrapper.appendChild(row);
        renumberParticipants();
    }

    // Hapus peserta
    wrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.removeParticipantBtn');
        if (btn) {
            btn.closest('.participant-row').remove();
            renumberParticipants();
        }
    });

    // Search dengan debounce
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = searchInput.value.trim();

        if (query.length < 2) {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(function () {
            fetch(`{{ route('users.search') }}?q=${encodeURIComponent(query)}&event_id=${eventId}`)
                .then(res => res.json())
                .then(users => {
                    searchResults.innerHTML = '';

                    if (users.length === 0) {
                        searchResults.innerHTML = `
                            <div class="list-group-item text-secondary small">
                                Tidak ditemukan user dengan kata kunci tersebut.
                            </div>`;
                        searchResults.style.display = 'block';
                        return;
                    }

                    users.forEach(function (user) {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        item.innerHTML = `
                            <div class="fw-semibold">${user.name}</div>
                            <div class="text-secondary small">${user.email}</div>
                        `;

                        item.addEventListener('click', function () {
                            addParticipant(user);
                            searchInput.value = '';
                            searchResults.style.display = 'none';
                            searchResults.innerHTML = '';
                        });

                        searchResults.appendChild(item);
                    });

                    searchResults.style.display = 'block';
                });
        }, 300); // debounce 300ms
    });

    // Tutup dropdown kalau klik di luar
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
    const form = document.getElementById('registrationForm');

    form.addEventListener('submit', function (e) {
        const rows = wrapper.querySelectorAll('.participant-row');

        if (rows.length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal 1 peserta sebelum mendaftar.');
            searchInput.focus();
        }
    });
    renumberParticipants();
})();
</script> 
@endpush