@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('events.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Tambah Data Event</h2>
                
            </div>
        </div>
    </div>
</div>
    <div class="page-body">
        <div class="container-xl">
            <div class="card shadow-sm border-0">
                <div class="card-body px-5 py-4">
                    @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-2">
                                    Terdapat kesalahan:
                                </div>

                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    <form id="eventForm" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card mb-4">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">Informasi Event</h3>
                                    <div class="text-secondary small">
                                        Informasi dasar mengenai event yang akan dibuat.
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label required">Nama Event</label>
                                        <input type="text"
                                            name="name"
                                            class="form-control"
                                            value="{{ old('name') }}"
                                            placeholder="Masukkan nama event"
                                            required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label required">Kategori</label>
                                        <select name="event_category_id"
                                                class="form-select select2"
                                                required>
                                            <option value="">Pilih Kategori</option>

                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('event_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label required">Jenis Event</label>

                                        <select id="event_type"
                                                name="event_type"
                                                class="form-select select2"
                                                required>

                                            @foreach($eventTypes as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ old('event_type', 'free') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Pembicara</label>

                                        <select name="speaker_ids[]"
                                                class="form-select select2"
                                                multiple>

                                            @foreach($speakers as $speaker)
                                                <option value="{{ $speaker->id }}"
                                                    {{ in_array($speaker->id, old('speaker_ids', [])) ? 'selected' : '' }}>
                                                    {{ $speaker->fullname }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <div class="form-hint">
                                            Pilih satu atau lebih pembicara untuk event ini.
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label required">Deskripsi Event</label>

                                        <textarea name="description"
                                            rows="5"
                                            class="form-control"
                                            placeholder="Tuliskan deskripsi lengkap mengenai event...">{{ old('description') }}</textarea>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div>
                                    <h4 class="mb-1">Jadwal Event</h4>
                                    <div class="text-secondary small">
                                        Atur periode pendaftaran dan waktu pelaksanaan event.
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">

                                    <div class="col-md-6">
                                        <label class="form-label">Registrasi Dibuka</label>
                                        <input type="datetime-local"
                                            name="registration_open"
                                            class="form-control"
                                            value="{{ old('registration_open') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Registrasi Ditutup</label>
                                        <input type="datetime-local"
                                            name="registration_close"
                                            class="form-control"
                                            value="{{ old('registration_close') }}">
                                    </div>

                                    {{-- Mulai Event --}}
                                    <div class="col-md-6">
                                        <label class="form-label required">Mulai Event</label>
                                        <input type="datetime-local"
                                            name="start_at"
                                            class="form-control"
                                            value="{{ old('start_at') }}"
                                            required>
                                    </div>

                                    {{-- Selesai Event --}}
                                    <div class="col-md-6">
                                        <label class="form-label required">Selesai Event</label>
                                        <input type="datetime-local"
                                            name="end_at"
                                            class="form-control"
                                            value="{{ old('end_at') }}"
                                            required>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div>
                                    <h4 class="mb-1">Lokasi & Tiket</h4>
                                    <div class="text-secondary small">
                                        Informasi lokasi, harga tiket, dan kapasitas peserta.
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-3">
                                        <label class="form-label">Lokasi</label>
                                        <input type="text"
                                            name="location"
                                            class="form-control"
                                            value="{{ old('location') }}"
                                            placeholder="Contoh: Hotel Fortuna Grande">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Link Google Maps</label>
                                        <input type="url"
                                            name="google_maps_url"
                                            class="form-control"
                                            value="{{ old('google_maps_url') }}"
                                            placeholder="https://maps.google.com/...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Harga Tiket</label>
                                        <input type="number"
                                            name="price"
                                            id="price"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('price', 0) }}"
                                            placeholder="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Kuota Peserta</label>
                                        <input type="number"
                                            name="quota"
                                            class="form-control"
                                            min="1"
                                            value="{{ old('quota') }}"
                                            placeholder="Tidak terbatas">
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div>
                                    <h4 class="mb-1">Audience & Publikasi</h4>
                                    <div class="text-secondary small">
                                        Tentukan siapa yang dapat mengikuti event dan status publikasinya.
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">Audience</label>
                                            <select name="audience_type" class="form-select select2" required>
                                                @foreach($audiences as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ old('audience_type', 'public') == $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status Publikasi</label>
                                        <select name="is_published"
                                                class="form-select select2"
                                                required>

                                            <option value="1"
                                                {{ old('is_published', '0') == '1' ? 'selected' : '' }}>
                                                Ya, Sudah publish
                                            </option>

                                            <option value="0"
                                                {{ old('is_published', '0') == '0' ? 'selected' : '' }}>
                                                Belum Publish
                                            </option>

                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h4 class="mb-1">Media Event</h4>
                            <div class="text-secondary small">
                                Upload poster dan thumbnail yang digunakan untuk menampilkan event.
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Poster</label>
                                <input type="file"
                                    name="poster"
                                    class="form-control"
                                    accept="image/*">

                                <div class="form-hint">
                                    Format gambar: JPG, JPEG, PNG, WEBP.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Thumbnail</label>
                                <input type="file"
                                    name="thumbnail"
                                    class="form-control"
                                    accept="image/*">

                                <div class="form-hint">
                                    Gunakan gambar dengan rasio yang sesuai untuk thumbnail.
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-12">

                                <label class="form-label">
                                    Video YouTube
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="ti ti-brand-youtube"></i>
                                    </span>

                                    <input type="url"
                                        name="youtube_url"
                                        id="youtube_url"
                                        class="form-control"
                                        value="{{ old('youtube_url') }}"
                                        placeholder="https://www.youtube.com/watch?v=...">

                                </div>

                                <div class="form-hint">
                                    Masukkan link video YouTube event.
                                </div>

                            </div>
                            <div class="col-12 mt-3">

                                <div id="youtubePreviewContainer"
                                    class="youtube-preview-container d-none">

                                    <div class="youtube-preview-header">

                                        <div>
                                            <div class="fw-semibold">
                                                Preview Video
                                            </div>

                                            <div class="text-secondary small">
                                                Video yang akan ditampilkan pada halaman event.
                                            </div>
                                        </div>

                                        <button type="button"
                                                class="btn btn-sm btn-ghost-secondary"
                                                id="removeYoutubePreview">

                                            <i class="ti ti-x"></i>

                                        </button>

                                    </div>

                                    <div class="youtube-preview-wrapper">

                                        <iframe id="youtubePreview"
                                                src=""
                                                title="Preview YouTube"
                                                frameborder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                allowfullscreen>
                                        </iframe>

                                    </div>

                                </div>

                            </div>
                        </div>
                        <div class="card mb-4">

                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">
                                        Gallery Event
                                    </h3>

                                    <div class="text-secondary small">
                                        Tambahkan foto dokumentasi event.
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <button type="button"
                                            class="btn btn-primary"
                                            id="addGalleryButton">

                                        <i class="ti ti-plus me-1"></i>
                                        Tambah Foto

                                    </button>
                                </div>
                            </div>

                            <div class="card-body">

                                <div id="newGalleryContainer"></div>

                                <input
                                    type="file"
                                    id="galleryInput"
                                    name="gallery_images[]"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    class="d-none"
                                >

                            </div>

                        </div>
                        <div class="card mb-4">

                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">
                                        Rundown Event
                                    </h3>

                                    <div class="text-secondary small">
                                        Susunan acara berdasarkan waktu pelaksanaan event.
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <button type="button"
                                            class="btn btn-primary"
                                            id="addRundownButton">

                                        <i class="ti ti-plus me-1"></i>
                                        Tambah Rundown

                                    </button>
                                </div>
                            </div>

                            <div class="card-body">

                                <div id="newRundownContainer">

                                    {{-- old rundowns / empty state kamu di sini --}}

                                </div>

                            </div>

                        </div>

                        <div class="card mb-4">

                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">
                                        FAQ Event
                                    </h3>

                                    <div class="text-secondary small">
                                        Pertanyaan dan jawaban yang sering ditanyakan peserta.
                                    </div>
                                </div>

                                <div class="card-actions">
                                    <button type="button"
                                            class="btn btn-primary"
                                            id="addFaqButton">

                                        <i class="ti ti-plus me-1"></i>
                                        Tambah FAQ

                                    </button>
                                </div>
                            </div>

                            <div class="card-body">

                                <div id="faqContainer">

                                    {{-- old FAQs / empty state kamu di sini --}}

                                </div>

                            </div>

                        </div>
                        <div class="card mb-4">

                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">
                                        Peluang Amal Shalih
                                    </h3>

                                    <div class="text-secondary small">
                                        Sponsorship / Dukungan Acara
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">

                                <div class="row g-3">
                                    <div class="col-md-6">

                                        <div class="card card-sm border h-100">

                                            <div class="card-body">

                                                <div class="d-flex align-items-center gap-3 mb-3">

                                                    <span class="avatar avatar-lg bg-success-lt">
                                                        <i class="ti ti-brand-whatsapp fs-2"></i>
                                                    </span>

                                                    <div>
                                                        <div class="fw-semibold">
                                                            Hubungi WA Admin
                                                        </div>

                                                        <div class="text-secondary small">
                                                            Hubungi admin untuk informasi sponsorship
                                                            dan dukungan acara.
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="input-group">

                                                    <input type="text"
                                                        name="sponsorship_whatsapp"
                                                        class="form-control"
                                                        value="{{ old('sponsorship_whatsapp') }}"
                                                        placeholder="Contoh: 628123456789">

                                                    <span class="input-group-text">
                                                        <i class="ti ti-phone"></i>
                                                    </span>

                                                </div>

                                                <div class="form-hint">
                                                    Masukkan nomor WhatsApp admin tanpa tanda +.
                                                </div>

                                            </div>

                                        </div>

                                    </div>
                                    <div class="col-md-6">

                                        <div class="card card-sm border h-100">

                                            <div class="card-body">

                                                <div class="d-flex align-items-center gap-3 mb-3">

                                                    <span class="avatar avatar-lg bg-primary-lt">
                                                        <i class="ti ti-qrcode fs-2"></i>
                                                    </span>

                                                    <div>
                                                        <div class="fw-semibold">
                                                            QRIS
                                                        </div>

                                                        <div class="text-secondary small">
                                                            Upload QRIS untuk dukungan acara.
                                                        </div>
                                                    </div>

                                                </div>

                                                <input type="file"
                                                    name="sponsorship_qris"
                                                    class="form-control"
                                                    accept="image/jpeg,image/png,image/webp">

                                                <div class="form-hint">
                                                    Format JPG, JPEG, PNG, atau WEBP.
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <div>
                                    <h3 class="card-title mb-1">
                                        Akun Keuangan Event
                                    </h3>

                                    <div class="text-secondary small">
                                        Tentukan akun yang digunakan untuk pencatatan transaksi event.
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-md-6">
                                        <label class="form-label required">
                                            Akun Kas / Bank
                                        </label>

                                        <select name="cash_account_id"
                                                class="form-select select2"
                                                required>

                                            <option value="">
                                                Pilih Akun Kas / Bank
                                            </option>

                                            @foreach($cashAccounts as $account)
                                                <option value="{{ $account->id }}"
                                                    {{ old('cash_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }}
                                                    - {{ $account->account_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <div class="form-hint">
                                            Akun yang digunakan untuk menerima pembayaran peserta.
                                        </div>
                                    </div>


                                    <div class="col-md-6">
                                        <label class="form-label required">
                                            Akun Pendapatan
                                        </label>

                                        <select name="income_account_id"
                                                class="form-select select2"
                                                required>

                                            <option value="">
                                                Pilih Akun Pendapatan
                                            </option>

                                            @foreach($incomeAccounts as $account)
                                                <option value="{{ $account->id }}"
                                                    {{ old('income_account_id') == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }}
                                                    - {{ $account->account_name }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <div class="form-hint">
                                            Akun pendapatan khusus untuk event ini.
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 pt-3 border-top">
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>
                                Simpan Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@vite('resources/js/pages/event-faq.js')
@push('js')
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%'
        });
        const $eventType = $('#event_type');
        const $price = $('#price');

        function handleEventType() {

            const type = $eventType.val();

            if (type === 'free') {

                // Event gratis → harga selalu 0
                $price.val(0);

                // Disable input harga
                $price.prop('disabled', true);

            } else {

                // Event berbayar → harga bisa diisi
                $price.prop('disabled', false);

                // Jika sebelumnya 0, kosongkan agar user langsung mengisi
                if ($price.val() === '0') {
                    $price.val('');
                }
            }
        }

        // Jalankan saat halaman pertama kali dibuka
        handleEventType();


        // Jalankan ketika jenis event berubah
        $eventType.on('change', function () {
            handleEventType();
        });

        $('form').on('submit', function () {

            if ($eventType.val() === 'free') {
                $price.prop('disabled', false);
                $price.val(0);
            }

        });
            const youtubeInput = document.getElementById('youtube_url');
    const youtubePreview = document.getElementById('youtubePreview');
    const youtubeIframe = document.getElementById('youtubeIframe');

    function getYoutubeEmbedUrl(url) {

        if (!url) {
            return null;
        }

        try {

            const parsedUrl = new URL(url);

            if (
                parsedUrl.hostname.includes('youtube.com') &&
                parsedUrl.searchParams.get('v')
            ) {
                return 'https://www.youtube.com/embed/' +
                    parsedUrl.searchParams.get('v');
            }

            if (parsedUrl.hostname === 'youtu.be') {

                const videoId = parsedUrl.pathname
                    .replace('/', '')
                    .split('?')[0];

                if (videoId) {

                    return 'https://www.youtube.com/embed/' +
                        videoId;
                }
            }

            if (
                parsedUrl.hostname.includes('youtube.com') &&
                parsedUrl.pathname.startsWith('/embed/')
            ) {

                const videoId = parsedUrl.pathname
                    .replace('/embed/', '')
                    .split('/')[0];

                if (videoId) {

                    return 'https://www.youtube.com/embed/' +
                        videoId;
                }
            }

        } catch (error) {

            return null;
        }

        return null;
    }


    function updateYoutubePreview() {

        if (
            !youtubeInput ||
            !youtubePreview ||
            !youtubeIframe
        ) {
            return;
        }

        const url = youtubeInput.value.trim();

        if (!url) {

            youtubePreview.classList.add('d-none');
            youtubeIframe.src = '';

            return;
        }

        const embedUrl = getYoutubeEmbedUrl(url);

        if (embedUrl) {

            youtubeIframe.src = embedUrl;
            youtubePreview.classList.remove('d-none');

        } else {

            youtubePreview.classList.add('d-none');
            youtubeIframe.src = '';
        }
    }


    if (youtubeInput) {

        youtubeInput.addEventListener(
            'input',
            updateYoutubePreview
        );

        youtubeInput.addEventListener(
            'change',
            updateYoutubePreview
        );
    }

    const addGalleryButton =
        document.getElementById('addGalleryButton');

    const newGalleryContainer =
        document.getElementById('newGalleryContainer');
    let galleryFiles = new DataTransfer();

    const eventForm = document.getElementById('eventForm');

    const galleryInput =
        document.getElementById('galleryInput');

    function renderGalleryPreview() {

        if (!newGalleryContainer) {
            return;
        }

        newGalleryContainer.innerHTML = '';


        if (galleryFiles.files.length === 0) {

            newGalleryContainer.innerHTML = `
                <div class="border rounded p-4 text-center text-secondary">
                    <i class="ti ti-photo-off fs-1 d-block mb-2"></i>
                    Belum ada foto gallery.
                </div>
            `;

            return;
        }

        const wrapper = document.createElement('div');

        wrapper.className =
            'd-flex gap-3 overflow-x-auto pb-2';


        Array.from(galleryFiles.files)
            .forEach((file, index) => {

                const card = document.createElement('div');

                card.className =
                    'position-relative flex-shrink-0';

                card.style.width = '180px';

                const imageWrapper = document.createElement('div');

                imageWrapper.className = 'position-relative rounded overflow-hidden border';

                imageWrapper.style.height = '120px';

                const image = document.createElement('img');

                image.className =
                    'w-100 h-100 object-fit-cover';

                image.alt =
                    file.name;

                const reader =
                    new FileReader();

                reader.onload = function (e) {

                    image.src = e.target.result;
                };

                reader.readAsDataURL(file);

                const removeButton = document.createElement('button');

                removeButton.type = 'button';

                removeButton.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-1';

                removeButton.innerHTML = '<i class="ti ti-trash"></i>';
                removeButton.title = 'Hapus foto';


                removeButton.addEventListener(
                    'click',
                    function () {

                        removeGalleryFile(index);
                    }
                );

                const fileName =
                    document.createElement('div');

                fileName.className =
                    'small text-truncate mt-2';

                fileName.title =
                    file.name;

                fileName.textContent =
                    file.name;

                imageWrapper.appendChild(image);

                imageWrapper.appendChild(removeButton);

                card.appendChild(imageWrapper);

                card.appendChild(fileName);

                wrapper.appendChild(card);
            });


        newGalleryContainer.appendChild(wrapper);
    }

    if (addGalleryButton) {

        addGalleryButton.addEventListener(
            'click',
            function () {

                if (!galleryInput) {
                    return;
                }

                galleryInput.click();
            }
        );
    }

    if (galleryInput) {

        galleryInput.addEventListener('change', function () {

            const files = Array.from(this.files);

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            files.forEach(function (file) {

                if (!allowedTypes.includes(file.type)) {
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    return;
                }

                const alreadyExists =
                    Array.from(galleryFiles.files).some(
                        function (existingFile) {
                            return (
                                existingFile.name === file.name &&
                                existingFile.size === file.size &&
                                existingFile.lastModified === file.lastModified
                            );
                        }
                    );

                if (!alreadyExists) {
                    galleryFiles.items.add(file);
                }
            });

            // Simpan seluruh file ke input asli
            this.files = galleryFiles.files;

            renderGalleryPreview();

        });
    }

    function removeGalleryFile(index) {

        const newFiles =
            new DataTransfer();


        Array.from(galleryFiles.files)
            .forEach(function (file, fileIndex) {

                if (fileIndex !== index) {

                    newFiles.items.add(file);
                }
            });


        galleryFiles =
            newFiles;


        if (galleryInput) {

            galleryInput.files =
                galleryFiles.files;
        }


        renderGalleryPreview();
    }

    renderGalleryPreview();

    if (eventForm) {

        eventForm.addEventListener(
            'submit',
            function () {

                if (galleryInput) {

                    galleryInput.files =
                        galleryFiles.files;
                }
            }
        );
    }
    });
</script>
<script>
const addRundownButton =
    document.getElementById('addRundownButton');

const newRundownContainer =
    document.getElementById('newRundownContainer');

let rundownIndex =
    newRundownContainer
        ? newRundownContainer.querySelectorAll('.rundown-create-card').length
        : 0;

function createRundownCard() {

    if (!newRundownContainer) {
        return;
    }

    const emptyState = document.getElementById('rundownEmptyState');

    if (emptyState) {
        emptyState.remove();
    }

    const index = rundownIndex++;

    const card = document.createElement('div');

    card.className = 'rundown-create-card mb-2 p-2 border rounded';

    card.dataset.rundownIndex = index;

    card.innerHTML = `
        <div class="row g-2 align-items-end">

            <div class="col-md-2">
                <label class="form-label small mb-1">
                    Tanggal
                </label>

                <input type="date"
                       name="rundowns[${index}][rundown_date]"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-1">
                <label class="form-label small mb-1">
                    Mulai
                </label>

                <input type="time"
                       name="rundowns[${index}][start_time]"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-1">
                <label class="form-label small mb-1">
                    Selesai
                </label>

                <input type="time"
                       name="rundowns[${index}][end_time]"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label small mb-1">
                    Aktivitas
                </label>

                <input type="text"
                       name="rundowns[${index}][activity]"
                       class="form-control form-control-sm"
                       placeholder="Contoh: Registrasi Peserta">
            </div>

            <div class="col-md-2">
                <label class="form-label small mb-1">
                    Pembicara/MC
                </label>

                <input type="text"
                       name="rundowns[${index}][speaker]"
                       class="form-control form-control-sm"
                       placeholder="Nama pembicara / MC">
            </div>

            <div class="col-md-2">
                <label class="form-label small mb-1">
                    Lokasi
                </label>

                <input type="text"
                       name="rundowns[${index}][location]"
                       class="form-control form-control-sm"
                       placeholder="Lokasi">
            </div>

            <div class="col-md-1">
                <button type="button"
                        class="btn btn-sm btn-danger w-100 rundown-remove-btn"
                        title="Hapus rundown">

                    <i class="ti ti-trash"></i>

                </button>
            </div>

        </div>
    `;

    newRundownContainer.appendChild(card);
}

if (addRundownButton) {

    addRundownButton.addEventListener(
        'click',
        function () {

            createRundownCard();

        }
    );

}

if (newRundownContainer) {

    newRundownContainer.addEventListener(
        'click',
        function (event) {

            const removeButton =
                event.target.closest('.rundown-remove-btn');

            if (!removeButton) {
                return;
            }

            const card =
                removeButton.closest('.rundown-create-card');

            if (card) {
                card.remove();
            }

            renderRundownEmptyState();

        }
    );

}

function renderRundownEmptyState() {

    if (!newRundownContainer) {
        return;
    }

    const cards =
        newRundownContainer.querySelectorAll(
            '.rundown-create-card'
        );

    if (cards.length === 0) {

        newRundownContainer.innerHTML = `
            <div id="rundownEmptyState"
                 class="event-gallery-empty">

                <i class="ti ti-list-details"></i>

                <div class="fw-semibold mt-2">
                    Belum ada rundown
                </div>

                <div class="text-secondary small">
                    Klik "Tambah Rundown" untuk menambahkan susunan acara.
                </div>

            </div>
        `;
    }

}
</script>
@endpush