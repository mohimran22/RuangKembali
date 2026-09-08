@extends('tablar::page')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col d-flex align-items-center">
                <a href="{{ route('events.index') }}" class="btn btn-primary d-flex align-items-center">
                    <i class="ti ti-arrow-left"></i>
                </a>
                
                    <h2 class="page-title mb-0">Ubah Data Event</h2>
                
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
                        <form action="{{ route('events.update', $event->id) }}"
                            method="POST"
                            enctype="multipart/form-data"
                            id="eventEditForm">

                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <h4 class="mb-1">Informasi Event</h4>
                                <div class="text-secondary small">
                                    Informasi dasar mengenai event.
                                </div>
                            </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label required">
                                            Nama Event
                                        </label>

                                        <input type="text"
                                            name="name"
                                            class="form-control"
                                            value="{{ old('name', $event->name) }}"
                                            placeholder="Masukkan nama event"
                                            required>
                                    </div>

                                    <div class="col-md-4">

                                        <label class="form-label required">
                                            Kategori
                                        </label>

                                        <select name="event_category_id"
                                                class="form-select select2"
                                                required>

                                            <option value="">
                                                Pilih Kategori
                                            </option>

                                            @foreach($categories as $category)

                                                <option value="{{ $category->id }}"
                                                    {{ old('event_category_id', $event->event_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>
                                    <div class="col-md-4">

                                        <label class="form-label required">
                                            Jenis Event
                                        </label>

                                        <select id="event_type"
                                                name="event_type"
                                                class="form-select select2"
                                                required>

                                            <option value="free"
                                                {{ old('event_type', $event->event_type) === 'free' ? 'selected' : '' }}>
                                                Gratis
                                            </option>

                                            <option value="paid"
                                                {{ old('event_type', $event->event_type) === 'paid' ? 'selected' : '' }}>
                                                Berbayar
                                            </option>

                                        </select>

                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Pembicara
                                        </label>

                                        <select name="speaker_ids[]"
                                                class="form-select select2"
                                                multiple>

                                            @foreach($speakers as $speaker)
                                                <option value="{{ $speaker->id }}"
                                                    {{ in_array($speaker->id, old('speaker_ids', $event->speakers->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                    {{ $speaker->fullname }}
                                                </option>
                                            @endforeach

                                        </select>

                                        <div class="form-hint">
                                            Pilih satu atau lebih pembicara untuk event ini.
                                        </div>
                                    </div>
                                </div>
                            <div class="mb-4">

                                <h4 class="mb-1">
                                    Jadwal Event
                                </h4>

                                <div class="text-secondary small">
                                    Atur periode pendaftaran dan waktu pelaksanaan event.
                                </div>

                            </div>

                            <div class="row g-3 mb-4">

                                {{-- Registrasi Dibuka --}}
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Registrasi Dibuka
                                    </label>

                                    <input type="date"
                                        name="registration_open"
                                        class="form-control"
                                        value="{{ old(
                                            'registration_open',
                                            $event->registration_open?->format('Y-m-d')
                                        ) }}">

                                </div>


                                {{-- Registrasi Ditutup --}}
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Registrasi Ditutup
                                    </label>

                                    <input type="date"
                                        name="registration_close"
                                        class="form-control"
                                        value="{{ old(
                                            'registration_close',
                                            $event->registration_close?->format('Y-m-d')
                                        ) }}">

                                </div>


                                {{-- Mulai Event --}}
                                <div class="col-md-6">

                                    <label class="form-label required">
                                        Mulai Event
                                    </label>

                                    <input type="datetime-local"
                                        name="start_at"
                                        class="form-control"
                                        value="{{ old(
                                            'start_at',
                                            $event->start_at?->format('Y-m-d\TH:i')
                                        ) }}"
                                        required>

                                </div>


                                {{-- Selesai Event --}}
                                <div class="col-md-6">

                                    <label class="form-label required">
                                        Selesai Event
                                    </label>

                                    <input type="datetime-local"
                                        name="end_at"
                                        class="form-control"
                                        value="{{ old(
                                            'end_at',
                                            $event->end_at?->format('Y-m-d\TH:i')
                                        ) }}"
                                        required>

                                </div>

                            </div>

                            <div class="mb-4">

                                <h4 class="mb-1">
                                    Lokasi & Tiket
                                </h4>

                                <div class="text-secondary small">
                                    Informasi lokasi, harga tiket, dan kapasitas peserta.
                                </div>

                            </div>

                            <div class="row g-3 mb-4">

                                {{-- Lokasi --}}
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Lokasi
                                    </label>

                                    <input type="text"
                                        name="location"
                                        class="form-control"
                                        value="{{ old('location', $event->location) }}"
                                        placeholder="Contoh: Hotel Fortuna Grande">

                                </div>


                                {{-- Harga --}}
                                <div class="col-md-3">

                                    <label class="form-label">
                                        Harga Tiket
                                    </label>

                                    <div class="input-group">

                                        <span class="input-group-text">
                                            Rp
                                        </span>

                                        <input type="number"
                                            name="price"
                                            id="price"
                                            class="form-control"
                                            min="0"
                                            step="0.01"
                                            value="{{ old('price', $event->price ?? 0) }}"
                                            placeholder="0">

                                    </div>

                                    <div class="form-hint">
                                        Untuk event gratis otomatis Rp 0.
                                    </div>

                                </div>


                                {{-- Kuota --}}
                                <div class="col-md-3">

                                    <label class="form-label">
                                        Kuota Peserta
                                    </label>

                                    <input type="number"
                                        name="quota"
                                        class="form-control"
                                        min="1"
                                        value="{{ old('quota', $event->quota) }}"
                                        placeholder="Tidak terbatas">

                                </div>

                            </div>

                            <div class="mb-4">

                                <h4 class="mb-1">
                                    Audience & Publikasi
                                </h4>

                                <div class="text-secondary small">
                                    Tentukan siapa yang dapat mengikuti event dan status publikasinya.
                                </div>

                            </div>

                            <div class="row g-3 mb-4">

                                {{-- Audience --}}
                                <div class="col-md-6">

                                    <label class="form-label required">
                                        Audience
                                    </label>

                                    <select name="audience_type"
                                            class="form-select select2"
                                            required>

                                        <option value="public"
                                            {{ old('audience_type', $event->audience_type) === 'public' ? 'selected' : '' }}>
                                            Umum
                                        </option>

                                        <option value="gender"
                                            {{ old('audience_type', $event->audience_type) === 'gender' ? 'selected' : '' }}>
                                            Berdasarkan Gender
                                        </option>

                                        <option value="age"
                                            {{ old('audience_type', $event->audience_type) === 'age' ? 'selected' : '' }}>
                                            Berdasarkan Usia
                                        </option>

                                    </select>

                                </div>


                                {{-- Publish --}}
                                <div class="col-md-6">

                                    <label class="form-label required">
                                        Status Publikasi
                                    </label>

                                    <select name="is_published"
                                            class="form-select select2"
                                            required>

                                        <option value="1"
                                            {{ old(
                                                'is_published',
                                                $event->is_published ? '1' : '0'
                                            ) === '1' ? 'selected' : '' }}>
                                            Ya, Publish
                                        </option>

                                        <option value="0"
                                            {{ old(
                                                'is_published',
                                                $event->is_published ? '1' : '0'
                                            ) === '0' ? 'selected' : '' }}>
                                            Belum Publish
                                        </option>

                                    </select>

                                </div>

                            </div>

                            <div class="mb-4">

                                <h4 class="mb-1">
                                    Media Event
                                </h4>

                                <div class="text-secondary small">
                                    Kelola poster, thumbnail, video YouTube, dan galeri event.
                                </div>

                            </div>

                            <div class="row g-4 mb-4">

                                {{-- Poster --}}
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Poster
                                    </label>

                                    <div class="media-preview-box mb-2"
                                        id="posterPreviewBox">

                                        @if($event->poster)

                                            <img src="{{ Storage::url($event->poster) }}"
                                                alt="Poster {{ $event->name }}"
                                                id="posterPreviewImage"
                                                class="media-preview-image">

                                        @else

                                            <div class="media-preview-empty"
                                                id="posterPreviewEmpty">

                                                <i class="ti ti-photo-off"></i>

                                                <span>
                                                    Belum ada poster
                                                </span>

                                            </div>

                                        @endif

                                    </div>

                                    <input type="file"
                                        name="poster"
                                        id="poster"
                                        class="form-control"
                                        accept="image/jpeg,image/png,image/webp">

                                    <div class="form-hint">
                                        Kosongkan jika tidak ingin mengganti poster.
                                        Maksimal 2 MB.
                                    </div>

                                </div>


                                {{-- Thumbnail --}}
                                <div class="col-md-6">

                                    <label class="form-label">
                                        Thumbnail
                                    </label>

                                    <div class="media-preview-box mb-2"
                                        id="thumbnailPreviewBox">

                                        @if($event->thumbnail)

                                            <img src="{{ Storage::url($event->thumbnail) }}"
                                                alt="Thumbnail {{ $event->name }}"
                                                id="thumbnailPreviewImage"
                                                class="media-preview-image">

                                        @else

                                            <div class="media-preview-empty"
                                                id="thumbnailPreviewEmpty">

                                                <i class="ti ti-photo-off"></i>

                                                <span>
                                                    Belum ada thumbnail
                                                </span>

                                            </div>

                                        @endif

                                    </div>

                                    <input type="file"
                                        name="thumbnail"
                                        id="thumbnail"
                                        class="form-control"
                                        accept="image/jpeg,image/png,image/webp">

                                    <div class="form-hint">
                                        Kosongkan jika tidak ingin mengganti thumbnail.
                                        Maksimal 2 MB.
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
                                            value="{{ old('youtube_url', $event->youtube_url ?? '') }}"
                                            placeholder="https://www.youtube.com/watch?v=...">

                                    </div>

                                    <div class="form-hint">
                                        Masukkan link video YouTube event.
                                    </div>

                                </div>


                                {{-- Preview YouTube --}}
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

                            <div class="mb-4">

                                <div class="d-flex flex-column flex-sm-row
                                            justify-content-between
                                            align-items-sm-center
                                            gap-2 mb-3">

                                    <div>

                                        <h5 class="mb-1">
                                            Gallery Event
                                        </h5>

                                        <div class="text-secondary small">
                                            Kelola foto dokumentasi event.
                                        </div>

                                    </div>

                                    <button type="button"
                                            class="btn btn-outline-primary"
                                            id="addGalleryButton">

                                        <i class="ti ti-plus me-1"></i>
                                        Tambah Foto

                                    </button>

                                </div>


                                {{-- Existing Gallery --}}
                                <div id="existingGalleryContainer">

                                    @if($event->galleries && $event->galleries->count())

                                        <div class="event-gallery-edit-grid">

                                            @foreach($event->galleries as $gallery)

                                                <div class="event-gallery-card"
                                                    data-gallery-id="{{ $gallery->id }}">

                                                    <div class="event-gallery-image-wrapper">

                                                        <img src="{{ Storage::url($gallery->image) }}"
                                                            alt="{{ $gallery->caption ?? 'Gallery Event' }}"
                                                            class="event-gallery-edit-image">

                                                        <button type="button"
                                                                class="event-gallery-delete"
                                                                data-gallery-id="{{ $gallery->id }}"
                                                                title="Hapus foto">

                                                            <i class="ti ti-trash"></i>

                                                        </button>

                                                    </div>

                                                    <div class="p-2">

                                                        <input type="text"
                                                            name="gallery_captions[{{ $gallery->id }}]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old(
                                                                'gallery_captions.' . $gallery->id,
                                                                $gallery->caption
                                                            ) }}"
                                                            placeholder="Caption foto">

                                                    </div>

                                                    {{-- Hidden delete field akan dibuat oleh JS --}}

                                                </div>

                                            @endforeach

                                        </div>

                                    @else

                                        <div class="event-gallery-empty"
                                            id="existingGalleryEmpty">

                                            <i class="ti ti-photo-off"></i>

                                            <div class="fw-semibold mt-2">
                                                Belum ada foto gallery
                                            </div>

                                            <div class="text-secondary small">
                                                Tambahkan foto dokumentasi event.
                                            </div>

                                        </div>

                                    @endif

                                </div>


                                {{-- Deleted Gallery IDs --}}
                                <div id="deletedGalleryContainer"></div>


                                {{-- New Gallery --}}
                                <div id="newGalleryContainer"
                                    class="mt-3">

                                    {{-- Dynamic content --}}

                                </div>

                            </div>

                            <div class="mb-4">

                                <h4 class="mb-1">
                                    Deskripsi Event
                                </h4>

                                <div class="text-secondary small">
                                    Jelaskan informasi lengkap mengenai event.
                                </div>

                            </div>

                            <div class="row mb-4">

                                <div class="col-12">

                                    <label class="form-label">
                                        Deskripsi
                                    </label>

                                    <textarea name="description"
                                            rows="7"
                                            class="form-control"
                                            placeholder="Tuliskan deskripsi lengkap mengenai event...">{{ old('description', $event->description) }}</textarea>

                                </div>

                            </div>

                            <div class="d-flex flex-column
                                        flex-sm-row
                                        justify-content-end
                                        gap-2
                                        pt-3
                                        border-top">

                                <a href="{{ route('events.index') }}"
                                class="btn btn-secondary">

                                    <i class="ti ti-arrow-left me-1"></i>
                                    Batal

                                </a>

                                <button type="submit"
                                        class="btn btn-primary">

                                    <i class="ti ti-device-floppy me-1"></i>
                                    Simpan Perubahan

                                </button>

                            </div>

                        </form>
                </div>
            </div>
        </div>
    </div>

@endsection
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

            // Pastikan event gratis selalu mengirim price = 0
            if ($eventType.val() === 'free') {
                $price.prop('disabled', false);
                $price.val(0);
            }

        });

    });
</script>
<script>

document.addEventListener('DOMContentLoaded', function () {

    const eventType = document.getElementById('event_type');
    const priceInput = document.getElementById('price');

    const posterInput = document.getElementById('poster');
    const thumbnailInput = document.getElementById('thumbnail');

    const youtubeInput = document.getElementById('youtube_url');
    const youtubePreviewContainer =
        document.getElementById('youtubePreviewContainer');

    const youtubePreview =
        document.getElementById('youtubePreview');

    const removeYoutubePreview =
        document.getElementById('removeYoutubePreview');

    const addGalleryButton =
        document.getElementById('addGalleryButton');

    const newGalleryContainer =
        document.getElementById('newGalleryContainer');

    const deletedGalleryContainer =
        document.getElementById('deletedGalleryContainer');

    function togglePrice() {

        if (!eventType || !priceInput) {
            return;
        }

        if (eventType.value === 'free') {

            priceInput.value = 0;

            priceInput.disabled = true;

            priceInput.classList.add('bg-light');

        } else {

            priceInput.disabled = false;

            priceInput.classList.remove('bg-light');

        }

    }

    if (eventType) {

        eventType.addEventListener(
            'change',
            togglePrice
        );

        togglePrice();

    }

    function previewImage(
        input,
        previewBox,
        imageId,
        emptyId
    ) {

        if (!input || !previewBox) {
            return;
        }

        input.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                this.value = '';

                alert('File harus berupa gambar.');

                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                let image =
                    document.getElementById(imageId);

                if (!image) {

                    image =
                        document.createElement('img');

                    image.id = imageId;

                    image.className =
                        'media-preview-image';

                    previewBox.innerHTML = '';

                    previewBox.appendChild(image);

                }

                image.src = event.target.result;

            };

            reader.readAsDataURL(file);

        });

    }

    previewImage(
        posterInput,
        document.getElementById('posterPreviewBox'),
        'posterPreviewImage',
        'posterPreviewEmpty'
    );


    previewImage(
        thumbnailInput,
        document.getElementById('thumbnailPreviewBox'),
        'thumbnailPreviewImage',
        'thumbnailPreviewEmpty'
    );

    function getYoutubeId(url) {

        if (!url) {
            return null;
        }

        try {

            const parsed =
                new URL(url);

            /*
             * youtube.com/watch?v=xxxxx
             */
            if (
                parsed.hostname.includes('youtube.com') &&
                parsed.searchParams.get('v')
            ) {

                return parsed.searchParams.get('v');

            }


            /*
             * youtu.be/xxxxx
             */
            if (
                parsed.hostname === 'youtu.be'
            ) {

                return parsed.pathname
                    .replace('/', '')
                    .split('/')[0];

            }


            /*
             * youtube.com/embed/xxxxx
             */
            if (
                parsed.pathname.startsWith('/embed/')
            ) {

                return parsed.pathname
                    .split('/embed/')[1]
                    .split('/')[0];

            }


            /*
             * youtube.com/shorts/xxxxx
             */
            if (
                parsed.pathname.startsWith('/shorts/')
            ) {

                return parsed.pathname
                    .split('/shorts/')[1]
                    .split('/')[0];

            }

        } catch (error) {

            return null;

        }

        return null;

    }


    function updateYoutubePreview() {

        if (!youtubeInput) {
            return;
        }

        const url =
            youtubeInput.value.trim();

        const videoId =
            getYoutubeId(url);

        if (!videoId) {

            youtubePreviewContainer
                .classList.add('d-none');

            youtubePreview.src = '';

            return;

        }

        youtubePreview.src =
            'https://www.youtube.com/embed/' +
            videoId;

        youtubePreviewContainer
            .classList.remove('d-none');

    }


    if (youtubeInput) {

        youtubeInput.addEventListener(
            'input',
            updateYoutubePreview
        );

        /*
         * Tampilkan preview ketika halaman
         * edit pertama kali dibuka.
         */
        updateYoutubePreview();

    }


    if (removeYoutubePreview) {

        removeYoutubePreview.addEventListener(
            'click',
            function () {

                youtubeInput.value = '';

                youtubePreview.src = '';

                youtubePreviewContainer
                    .classList.add('d-none');

            }
        );

    }

    document
        .querySelectorAll('.event-gallery-delete')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const galleryId =
                        this.dataset.galleryId;

                    const card =
                        document.querySelector(
                            '.event-gallery-card[data-gallery-id="' +
                            galleryId +
                            '"]'
                        );

                    if (!card) {
                        return;
                    }


                    /*
                     * Kalau sudah ditandai hapus,
                     * batalkan penghapusan.
                     */

                    if (
                        card.classList.contains(
                            'is-deleted'
                        )
                    ) {

                        card.classList.remove(
                            'is-deleted'
                        );

                        const hiddenInput =
                            document.querySelector(
                                'input[name="delete_gallery_ids[]"][value="' +
                                galleryId +
                                '"]'
                            );

                        if (hiddenInput) {
                            hiddenInput.remove();
                        }

                        this.innerHTML =
                            '<i class="ti ti-trash"></i>';

                        return;
                    }


                    /*
                     * Tandai sebagai deleted
                     */

                    card.classList.add(
                        'is-deleted'
                    );

                    this.innerHTML =
                        '<i class="ti ti-arrow-back-up"></i>';


                    /*
                     * Buat hidden input
                     */

                    const hiddenInput =
                        document.createElement('input');

                    hiddenInput.type = 'hidden';

                    hiddenInput.name =
                        'delete_gallery_ids[]';

                    hiddenInput.value =
                        galleryId;

                    deletedGalleryContainer
                        .appendChild(hiddenInput);

                }
            );

        });

    if (addGalleryButton) {

        addGalleryButton.addEventListener(
            'click',
            function () {

                const input =
                    document.createElement('input');

                input.type = 'file';

                input.name =
                    'gallery_images[]';

                input.accept =
                    'image/jpeg,image/png,image/webp';

                input.multiple = true;

                input.className =
                    'd-none';

                document.body.appendChild(input);


                input.addEventListener(
                    'change',
                    function () {

                        const files =
                            Array.from(this.files);

                        if (!files.length) {
                            input.remove();
                            return;
                        }


                        let wrapper =
                            document.querySelector(
                                '#newGalleryPreviewList'
                            );


                        if (!wrapper) {

                            wrapper =
                                document.createElement('div');

                            wrapper.id =
                                'newGalleryPreviewList';

                            wrapper.className =
                                'new-gallery-list';

                            newGalleryContainer
                                .appendChild(wrapper);

                        }


                        files.forEach(function (file) {

                            if (
                                !file.type.startsWith(
                                    'image/'
                                )
                            ) {
                                return;
                            }


                            const reader =
                                new FileReader();


                            reader.onload =
                                function (event) {

                                    const item =
                                        document.createElement(
                                            'div'
                                        );

                                    item.className =
                                        'new-gallery-item';


                                    const image =
                                        document.createElement(
                                            'img'
                                        );

                                    image.src =
                                        event.target.result;

                                    image.className =
                                        'new-gallery-preview';


                                    const removeButton =
                                        document.createElement(
                                            'button'
                                        );

                                    removeButton.type =
                                        'button';

                                    removeButton.className =
                                        'new-gallery-remove';

                                    removeButton.innerHTML =
                                        '<i class="ti ti-x"></i>';


                                    removeButton.addEventListener(
                                        'click',
                                        function () {

                                            item.remove();

                                            /*
                                             * Jika sudah tidak ada
                                             * preview baru, bersihkan
                                             */
                                            if (
                                                !wrapper
                                                    .children.length
                                            ) {

                                                wrapper.remove();

                                            }

                                        }
                                    );


                                    item.appendChild(
                                        image
                                    );

                                    item.appendChild(
                                        removeButton
                                    );

                                    wrapper.appendChild(
                                        item
                                    );

                                };


                            reader.readAsDataURL(file);

                        });


                        /*
                         * Masukkan input file ke container
                         * agar ikut submit.
                         */

                        newGalleryContainer
                            .appendChild(input);

                    }
                );


                input.click();

            }
        );

    }

});

</script>
@endpush