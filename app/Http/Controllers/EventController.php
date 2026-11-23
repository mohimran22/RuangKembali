<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use App\Models\EventGallery;
use App\Models\Customer;
use App\Models\Affiliator;
use App\Models\EventFaq;
use App\Models\Invoice;
use App\Models\EventRundown;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use App\Models\AccountingAccount;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventYoutubeLink;
use App\Models\User;
use App\Helpers\ActiveRole;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    private function authorizePermission(string $permission): void
{
    abort_unless(ActiveRole::hasPermission($permission), 403);
}

private function isEventStaff(): bool
{
    return in_array(ActiveRole::name(), ['Super-Admin', 'Tim']);
}

public function index(Request $request)
{
    $auth = auth()->user();

    $query = Event::with([
        'category:id,name',
    ]);

    if (
        $auth->can('lihat data event') &&
        !$auth->can('lihat daftar event')
    ) {
        // Jika memang event nantinya mempunyai owner/user tertentu,
        // tambahkan pembatasan di sini.
    }

    if ($request->ajax()) {

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('event_code', function ($event) {
                return e($event->event_code);
            })

            ->addColumn('event_name', function ($event) {
                $url = route('events.manage', $event->slug);

                return '<a href="' . $url . '">'
                    . e(Str::title($event->name ?? '-'))
                    . '</a>';
            })

            ->addColumn('event_category', function ($event) {
                return $event->category?->name ?? '-';
            })

            ->editColumn('event_type', function ($event) {

                if ($event->event_type === 'paid') {
                    return '<span class="badge bg-primary">Berbayar</span>';
                }

                return '<span class="badge bg-success">Gratis</span>';
            })

            ->addColumn('schedule', function ($event) {

                if (!$event->start_at) {
                    return '-';
                }

                $start = $event->start_at->translatedFormat('d M Y H:i');

                if (!$event->end_at) {
                    return $start;
                }

                $end = $event->end_at->translatedFormat('d M Y H:i');

                return $start .
                    '<br><small class="text-secondary">s/d ' .
                    $end .
                    '</small>';
            })

            ->addColumn('registration', function ($event) {

                if (
                    !$event->registration_open &&
                    !$event->registration_close
                ) {
                    return '-';
                }

                $open = $event->registration_open
                    ? $event->registration_open->translatedFormat('d M Y')
                    : '-';

                $close = $event->registration_close
                    ? $event->registration_close->translatedFormat('d M Y')
                    : '-';

                return $open .
                    '<br><small class="text-secondary">s/d ' .
                    $close .
                    '</small>';
            })

            ->editColumn('price', function ($event) {

                if ((float) $event->price <= 0) {
                    return '<span class="badge bg-success">Gratis</span>';
                }

                return 'Rp ' . number_format(
                    $event->price,
                    0,
                    ',',
                    '.'
                );
            })

            ->editColumn('quota', function ($event) {

                if (is_null($event->quota)) {
                    return '<span class="text-secondary">Tidak terbatas</span>';
                }

                return $event->remaining_quota .
                    ' / ' .
                    $event->quota;
            })

            ->addColumn('status', function ($event) {

                return match ($event->status_label) {

                    'Coming Soon' =>
                        '<span class="badge bg-secondary">
                            Coming Soon
                        </span>',

                    'Pendaftaran' =>
                        '<span class="badge bg-success">
                            Pendaftaran
                        </span>',

                    'Sold Out' =>
                        '<span class="badge bg-danger">
                            Sold Out
                        </span>',

                    'Sedang Berlangsung' =>
                        '<span class="badge bg-warning text-dark">
                            Berlangsung
                        </span>',

                    'Selesai' =>
                        '<span class="badge bg-dark">
                            Selesai
                        </span>',

                    default =>
                        '<span class="badge bg-secondary">
                            -
                        </span>',
                };
            })

            ->addColumn('action', function ($event) {

                $buttons = '<div class="btn-list">';

                if (ActiveRole::hasPermission('lihat data event')) {
                    $buttons .= '
                        <a href="' . route('events.manage', $event->slug) . '"
                           class="btn btn-icon btn-sm btn-primary"
                           title="Detail">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';
                }

                if (ActiveRole::hasPermission('ubah data event')) {
                    $buttons .= '
                        <a href="' . route('events.edit', $event->slug) . '"
                           class="btn btn-icon btn-sm btn-warning"
                           title="Edit">
                            <i class="ti ti-edit"></i>
                        </a>
                    ';
                }

                if (ActiveRole::hasPermission('hapus data event')) {
                    $buttons .= '
                        <button type="button"
                                data-id="' . $event->id . '"
                                class="btn btn-icon btn-sm btn-danger delete-events"
                                title="Hapus">
                            <i class="ti ti-trash"></i>
                        </button>
                    ';
                }

                $buttons .= '</div>';

                return $buttons;
            })

            ->rawColumns([
                'event_name',
                'event_type',
                'schedule',
                'registration',
                'price',
                'quota',
                'status',
                'action',
            ])

            ->make(true);
    }

    return view('events.index');
}

public function create()
{
    $this->authorizePermission('tambah data event');

    $categories = EventCategory::orderBy('name')->get();

    $speakers = User::where('is_speakers', true)->get();

    $eventTypes = [
        'free' => 'Gratis',
        'paid' => 'Berbayar',
    ];

    $audiences = [
        'public' => 'Umum',
        'gender' => 'Berdasarkan Gender',
        'age' => 'Berdasarkan Usia',
    ];
    $statuses = [
        'coming_soon' => 'Coming Soon',
        'registration_open' => 'Pendaftaran',
        'sold_out' => 'Sold Out',
        'ongoing' => 'Sedang Berlangsung',
        'finished' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $cashAccounts = AccountingAccount::where(
        'license_id',
        config('app.license_id')
    )
    ->where('category', 'AKTIVA')
    ->where('sub_category', 'Aset Lancar - Kas & Bank')
    ->where('is_parent', false)
    ->where('is_active', true)
    ->orderBy('account_code')
    ->get();

    $incomeAccounts = AccountingAccount::where(
        'license_id',
        config('app.license_id')
    )
    ->where('category', 'PENDAPATAN')
    ->where('is_parent', false)
    ->where('is_active', true)
    ->orderBy('account_code')
    ->get();
    return view('events.create', compact(
        'categories',
        'speakers',
        'eventTypes',
        'audiences',
        'statuses',
        'cashAccounts',
        'incomeAccounts'
    ));
}

public function store(Request $request)
{
    $this->authorizePermission('tambah data event');

    $request->validate([
        'name' => 'required|string|max:255',
        'event_category_id' => 'required|exists:event_categories,id',
        'event_type' => 'required|in:free,paid',
        'audience_type' => 'required|in:public,gender,age',
        'registration_open' => 'nullable|date',
        'registration_close' => 'nullable|date|after_or_equal:registration_open',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after:start_at',
        'location' => 'nullable|string|max:255',
        'price' => [
            'required_if:event_type,paid',
            'nullable',
            'numeric',
            'min:0',
        ],
        'transfer_bank_name' => ['required_if:event_type,paid', 'nullable', 'string', 'max:100'],
        'transfer_account_number' => [
            'required_if:event_type,paid',
            'nullable',
            'string',
            'max:50',
            'regex:/^[0-9][0-9\s\-]*$/',
        ],
        'transfer_account_holder' => ['required_if:event_type,paid', 'nullable', 'string', 'max:150'],
        'cash_account_id' => [
            'required',
            'uuid',
            'exists:accounting_accounts,id',
        ],
        'income_account_id' => [
            'required',
            'uuid',
            'exists:accounting_accounts,id',
        ],
        'quota' => 'nullable|integer|min:1',
        'description' => 'nullable|string',
        'is_published' => 'required|boolean',
        'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'youtube_links' => 'nullable|array',
        'youtube_links.*.url' => 'required|url|max:500',
        'youtube_links.*.title' => 'nullable|string|max:255',
        'gallery_images' => 'nullable|array',
        'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        'speaker_ids' => 'nullable|array',
        'speaker_ids.*' => 'exists:users,id',
        'google_maps_url' => 'nullable|url|max:2048',
        'rundowns' => 'nullable|array',
        'rundowns.*.rundown_date' => 'required|date',
        'rundowns.*.start_time' => 'required|date_format:H:i',
        'rundowns.*.end_time' => 'required|date_format:H:i',
        'rundowns.*.activity' => 'required|string|max:255',
        'rundowns.*.speaker' => 'nullable|string|max:255',
        'rundowns.*.location' => 'nullable|string|max:255',
        'faqs' => 'nullable|array',
        'faqs.*.question' => 'required|string|max:255',
        'faqs.*.answer' => 'required|string',
        'sponsorship_whatsapp' => 'nullable|string|max:30',
        'sponsorship_qris'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'has_certificate'      => 'nullable|boolean',
        'certificate_template' => ['required_if:has_certificate,1', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        'name_top'             => 'nullable|integer|between:0,100',
        'name_size'            => 'nullable|integer|between:12,100',
        'name_color'           => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
    ]);

    DB::beginTransaction();
    $uploadedFiles = [];

    try {
        $poster = null;
        if ($request->hasFile('poster')) {
            $poster = $request->file('poster')->store('events/posters', 'public');
            $uploadedFiles[] = $poster;
        }

        $thumbnail = null;

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $request->file('thumbnail')->store('events/thumbnails', 'public');
            $uploadedFiles[] = $thumbnail;
        }

        $event = Event::create([
            'event_code' => $this->generateEventCode(),
            'name' => $request->name,
            'event_category_id' => $request->event_category_id,
            'event_type' => $request->event_type,
            'audience_type' => $request->audience_type,
            'registration_open' => $request->registration_open,
            'registration_close' => $request->registration_close,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'location' => $request->location,
            'google_maps_url' => $request->google_maps_url,
            'price' => $request->event_type === 'free'
                ? 0
                : ($request->price ?? 0),
            'transfer_bank_name'      => $request->event_type === 'free' ? null : $request->transfer_bank_name,
            'transfer_account_number' => $request->event_type === 'free' ? null : $request->transfer_account_number,
            'transfer_account_holder' => $request->event_type === 'free' ? null : $request->transfer_account_holder,
            'quota' => $request->quota,
            'poster' => $poster,
            'thumbnail' => $thumbnail,
            'youtube_url' => $request->youtube_url,
            'description' => $request->description,
            'is_published' => $request->boolean('is_published'),
            'cash_account_id'   => $request->cash_account_id,
            'income_account_id' => $request->income_account_id,
            'sponsorship_whatsapp' => $request->sponsorship_whatsapp,
        ]);
        $event->speakers()->sync($request->speaker_ids ?? []);
        if ($request->filled('rundowns')) {

            foreach ($request->rundowns as $index => $rundown) {

                $event->rundowns()->create([
                    'rundown_date' => $rundown['rundown_date'],
                    'start_time' => $rundown['start_time'],
                    'end_time' => $rundown['end_time'],
                    'activity' => $rundown['activity'],
                    'speaker' => $rundown['speaker'] ?? null,
                    'location' => $rundown['location'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }
        if ($request->filled('faqs')) {

            foreach ($request->faqs as $index => $faq) {

                $event->faqs()->create([
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                    'sort_order' => $index + 1,
                ]);
            }
        }
        if ($request->filled('youtube_links')) {
            foreach ($request->youtube_links as $index => $link) {
                $event->youtubeLinks()->create([
                    'url' => $link['url'],
                    'title' => $link['title'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }
        if ($request->hasFile('gallery_images')) {
            foreach (
                $request->file('gallery_images')
                as $index => $image
            ) {
                $path = $image->store(
                    'events/galleries',
                    'public'
                );
                $uploadedFiles[] = $path;
                EventGallery::create([
                    'event_id' => $event->id,
                    'image' => $path,
                    'caption' => null,
                    'sort_order' =>$index + 1,
                ]);
            }
        }
        if ($request->hasFile('sponsorship_qris')) {
            $qris = $request->file('sponsorship_qris')->store('events/qris', 'public');
            $uploadedFiles[] = $qris;
            $event->update(['sponsorship_qris' => $qris]);
        }
        $this->saveCertificate($request, $event, $uploadedFiles);
        DB::commit();

        return redirect()
            ->route('events.index')
            ->with(
                'success',
                'Event berhasil ditambahkan.'
            );

    } catch (\Throwable $e) {

        DB::rollBack();

        foreach ($uploadedFiles as $file) {

            Storage::disk('public')
                ->delete($file);
        }
        report($e);
        return back()
            ->withInput()
            ->with(
                'error',
                'Event gagal ditambahkan. Silakan coba lagi.'
            );
    }
}

private function generateEventCode(): string
{
    $prefix = 'EVT' . now()->format('Y');

    $last = Event::where('event_code', 'like', $prefix . '%')
        ->orderByDesc('event_code')
        ->first();

    if (!$last) {
        return $prefix . '001';
    }

    $number = (int) substr($last->event_code, -3) + 1;

    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}
public function edit(Event $event)
{
    $this->authorizePermission('ubah data event');

    $categories = EventCategory::orderBy('name')->get();

    $eventTypes = [
        'free' => 'Gratis',
        'paid' => 'Berbayar',
    ];

    $audiences = [
        'public' => 'Umum',
        'gender' => 'Berdasarkan Gender',
        'age' => 'Berdasarkan Usia',
    ];
    $statuses = [
        'coming_soon' => 'Coming Soon',
        'registration_open' => 'Pendaftaran',
        'sold_out' => 'Sold Out',
        'ongoing' => 'Sedang Berlangsung',
        'finished' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $speakers = User::where('is_speakers', true)->get();
    $cashAccounts = AccountingAccount::where(
        'license_id',
        config('app.license_id')
    )
    ->where('category', 'AKTIVA')
    ->where('sub_category', 'Aset Lancar - Kas & Bank')
    ->where('is_parent', false)
    ->where('is_active', true)
    ->orderBy('account_code')
    ->get();

    $incomeAccounts = AccountingAccount::where(
        'license_id',
        config('app.license_id')
    )
    ->where('category', 'PENDAPATAN')
    ->where('is_parent', false)
    ->where('is_active', true)
    ->orderBy('account_code')
    ->get();
    return view('events.edit', compact(
        'event',
        'categories',
        'eventTypes',
        'audiences',
        'statuses',
        'speakers',
        'cashAccounts',
        'incomeAccounts'
    ));
}

public function update(Request $request, Event $event)
{
    $this->authorizePermission('ubah data event');

    $request->validate([
        'name' => 'required|string|max:255',
        'event_category_id' => 'required|exists:event_categories,id',
        'event_type' => 'required|in:free,paid',
        'audience_type' => 'required|in:public,gender,age',
        'registration_open' => 'nullable|date',
        'registration_close' => 'nullable|date|after_or_equal:registration_open',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after:start_at',
        'location' => 'nullable|string|max:255',
        'price' => 'nullable|numeric|min:0',
        'transfer_bank_name' => ['required_if:event_type,paid', 'nullable', 'string', 'max:100'],
        'transfer_account_number' => [
            'required_if:event_type,paid',
            'nullable',
            'string',
            'max:50',
            'regex:/^[0-9][0-9\s\-]*$/',
        ],
        'transfer_account_holder' => ['required_if:event_type,paid', 'nullable', 'string', 'max:150'],
        'quota' => 'nullable|integer|min:1',
        'is_published' => 'required|boolean',
        'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'youtube_links' => 'nullable|array',
        'youtube_links.*.id' => [
            'nullable',
            'exists:event_youtube_links,id',
        ],
        'youtube_links.*.url' => 'required|url|max:500',
        'youtube_links.*.title' => 'nullable|string|max:255',
        'gallery_images' => 'nullable|array',
        'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        'delete_gallery_ids' => 'nullable|array',
        'delete_gallery_ids.*' => 'exists:event_galleries,id',
        'gallery_captions' => 'nullable|array',
        'gallery_captions.*' =>  'nullable|string|max:255',
        'speaker_ids' => 'nullable|array',
        'speaker_ids.*' => 'exists:users,id',

        'google_maps_url' => 'nullable|url|max:2048',

        'cash_account_id' => [
            'required',
            'uuid',
            'exists:accounting_accounts,id',
        ],
        'income_account_id' => [
            'required',
            'uuid',
            'exists:accounting_accounts,id',
        ],
        'rundowns' => 'nullable|array',

        'rundowns.*.id' => [
            'nullable',
            'uuid',
            'exists:event_rundowns,id',
        ],

        'rundowns.*.rundown_date' => [
            'required',
            'date',
        ],

        'rundowns.*.start_time' => [
            'required',
            'date_format:H:i',
        ],

        'rundowns.*.end_time' => [
            'required',
            'date_format:H:i',
        ],

        'rundowns.*.activity' => [
            'required',
            'string',
            'max:255',
        ],

        'rundowns.*.speaker' => [
            'nullable',
            'string',
            'max:255',
        ],

        'rundowns.*.location' => [
            'nullable',
            'string',
            'max:255',
        ],

        'delete_rundown_ids' => 'nullable|array',
        'delete_rundown_ids.*' => 'exists:event_rundowns,id',
        'sponsorship_whatsapp' => 'nullable|string|max:30',
        'sponsorship_qris' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'remove_sponsorship_qris' => 'nullable|boolean',
        'faqs' => 'nullable|array',
        'faqs.*.question' => 'required|string|max:255',
        'faqs.*.answer' => 'required|string',
        'has_certificate'      => 'nullable|boolean',
        'certificate_template' => [
            Rule::requiredIf(fn () => $request->boolean('has_certificate') && ! $event->certificate?->template),
            'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096',
        ],
        'name_top'             => 'nullable|integer|between:0,100',
        'name_size'            => 'nullable|integer|between:12,100',
        'name_color'           => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
    ]);

    $eventStart = Carbon::parse($request->start_at);
    $eventEnd   = Carbon::parse($request->end_at);

    foreach ($request->input('rundowns', []) as $index => $rundown) {

        if (
            empty($rundown['rundown_date']) ||
            empty($rundown['start_time']) ||
            empty($rundown['end_time'])
        ) {
            continue;
        }

        $rundownStart = Carbon::parse(
            $rundown['rundown_date'] . ' ' . $rundown['start_time']
        );

        $rundownEnd = Carbon::parse(
            $rundown['rundown_date'] . ' ' . $rundown['end_time']
        );

        if ($rundownEnd->lte($rundownStart)) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                "rundowns.$index.end_time" =>
                    'Jam selesai rundown harus lebih besar dari jam mulai.',
            ]);
        }

        if ($rundownStart->lt($eventStart)) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                "rundowns.$index.start_time" =>
                    'Rundown tidak boleh dimulai sebelum event dimulai pada '
                    . $eventStart->format('d/m/Y H:i') . '.',
            ]);
        }

        if ($rundownEnd->gt($eventEnd)) {

            throw \Illuminate\Validation\ValidationException::withMessages([
                "rundowns.$index.end_time" =>
                    'Rundown tidak boleh berakhir setelah event selesai pada '
                    . $eventEnd->format('d/m/Y H:i') . '.',
            ]);
        }
    }

    DB::beginTransaction();

    $newFiles = [];

    try {

        $eventData = [
            'name' => $request->name,
            'event_category_id' => $request->event_category_id,
            'event_type' => $request->event_type,
            'audience_type' => $request->audience_type,
            'registration_open' => $request->registration_open,
            'registration_close' => $request->registration_close,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
            'location' => $request->location,
            'google_maps_url' => $request->google_maps_url,
            'price' => $request->event_type === 'free'
                ? 0
                : ($request->price ?? 0),
            'transfer_bank_name'      => $request->event_type === 'free' ? null : $request->transfer_bank_name,
            'transfer_account_number' => $request->event_type === 'free' ? null : $request->transfer_account_number,
            'transfer_account_holder' => $request->event_type === 'free' ? null : $request->transfer_account_holder,
            'quota' => $request->quota,
            'description' => $request->description,
            'is_published' => $request->boolean('is_published'),
            'sponsorship_whatsapp' => $request->sponsorship_whatsapp,
            'cash_account_id'   => $request->cash_account_id,
            'income_account_id' => $request->income_account_id,
        ];

        if ($request->hasFile('poster')) {

            $oldPoster = $event->poster;

            $poster = $request->file('poster')->store('events/posters', 'public');

            $newFiles[] = $poster;

            $eventData['poster'] = $poster;

            if ($oldPoster) {
                Storage::disk('public')
                    ->delete($oldPoster);
            }
        }

        if ($request->hasFile('thumbnail')) {

            $oldThumbnail = $event->thumbnail;

            $thumbnail = $request->file('thumbnail')->store('events/thumbnails', 'public');

            $newFiles[] = $thumbnail;

            $eventData['thumbnail'] = $thumbnail;

            if ($oldThumbnail) {
                Storage::disk('public')
                    ->delete($oldThumbnail);
            }
        }

        if ($request->hasFile('sponsorship_qris')) {

            $oldQris = $event->sponsorship_qris;

            $qris = $request->file('sponsorship_qris')
                ->store('events/qris', 'public');

            $newFiles[] = $qris;

            $eventData['sponsorship_qris'] = $qris;

            if ($oldQris) {
                Storage::disk('public')
                    ->delete($oldQris);
            }

        } elseif ($request->boolean('remove_sponsorship_qris')) {

            if ($event->sponsorship_qris) {

                Storage::disk('public')
                    ->delete($event->sponsorship_qris);
            }

            $eventData['sponsorship_qris'] = null;
        }
        $event->update($eventData);
        $oldCertTemplate = $this->saveCertificate($request, $event, $newFiles);
        $event->speakers()->sync($request->speaker_ids ?? []);
        if ($request->filled('delete_gallery_ids')) {

            $galleries = EventGallery::where('event_id', $event->id)
                ->whereIn(
                    'id',
                    $request->delete_gallery_ids
                )
                ->get();

            foreach ($galleries as $gallery) {

                if ($gallery->image) {

                    Storage::disk('public')
                        ->delete($gallery->image);
                }
                $gallery->delete();
            }
        }

        if ($request->filled('gallery_captions')) {

            foreach (
                $request->gallery_captions
                as $galleryId => $caption
            ) {

                EventGallery::where('id', $galleryId)
                    ->where('event_id', $event->id)
                    ->update([
                        'caption' => $caption,
                    ]);
            }
        }
        
        if ($request->filled('delete_rundown_ids')) {

            EventRundown::where('event_id', $event->id)
                ->whereIn('id', $request->delete_rundown_ids)
                ->delete();
        }

        $rundowns = $request->input('rundowns', []);

        if (!empty($rundowns)) {

            $lastOrder = EventRundown::where('event_id', $event->id)
                ->max('sort_order') ?? 0;

            $newIndex = 0;

            foreach ($rundowns as $data) {

                // Lewati baris kosong
                if (
                    empty($data['rundown_date']) &&
                    empty($data['start_time']) &&
                    empty($data['end_time']) &&
                    empty($data['activity'])
                ) {
                    continue;
                }

                if (!empty($data['id'])) {

                    EventRundown::where('id', $data['id'])
                        ->where('event_id', $event->id)
                        ->update([
                            'rundown_date' => $data['rundown_date'],
                            'start_time'   => $data['start_time'],
                            'end_time'     => $data['end_time'],
                            'activity'     => $data['activity'],
                            'speaker'      => $data['speaker'] ?? null,
                            'location'     => $data['location'] ?? null,
                        ]);

                }

                else {

                    EventRundown::create([
                        'event_id'      => $event->id,
                        'rundown_date'  => $data['rundown_date'],
                        'start_time'    => $data['start_time'],
                        'end_time'      => $data['end_time'],
                        'activity'      => $data['activity'],
                        'speaker'       => $data['speaker'] ?? null,
                        'location'      => $data['location'] ?? null,
                        'sort_order'    => $lastOrder + $newIndex + 1,
                    ]);

                    $newIndex++;
                }
            }
        }
        if ($request->hasFile('gallery_images')) {

            $lastOrder = EventGallery::where(
                'event_id',
                $event->id
            )->max('sort_order');

            $lastOrder = $lastOrder ?? 0;

            foreach (
                $request->file('gallery_images')
                as $index => $image
            ) {

                $path = $image->store(
                    'events/galleries',
                    'public'
                );

                $newFiles[] = $path;

                EventGallery::create([
                    'event_id' =>
                        $event->id,

                    'image' =>
                        $path,

                    'caption' =>
                        null,

                    'sort_order' =>
                        $lastOrder + $index + 1,
                ]);
            }
        }

        $submittedFaqIds = [];

        if ($request->filled('faqs')) {

            foreach ($request->faqs as $index => $faqData) {

                // FAQ lama
                if (!empty($faqData['id'])) {

                    $faq = EventFaq::where('id', $faqData['id'])
                        ->where('event_id', $event->id)
                        ->first();

                    if ($faq) {

                        $faq->update([
                            'question'  => $faqData['question'],
                            'answer'    => $faqData['answer'],
                            'sort_order' => $faqData['sort_order'] ?? $index,
                        ]);

                        $submittedFaqIds[] = $faq->id;
                    }

                } else {

                    // FAQ baru
                    $faq = EventFaq::create([
                        'event_id'   => $event->id,
                        'question'   => $faqData['question'],
                        'answer'     => $faqData['answer'],
                        'sort_order' => $faqData['sort_order'] ?? $index,
                    ]);

                    $submittedFaqIds[] = $faq->id;
                }
            }
        }

        $faqQuery = EventFaq::where('event_id', $event->id);

        if (count($submittedFaqIds)) {

            $faqQuery->whereNotIn('id', $submittedFaqIds);

        }

        $faqQuery->delete();
        $submittedYoutubeIds = [];

        if ($request->filled('youtube_links')) {

            foreach ($request->youtube_links as $index => $link) {

                if (empty($link['url'])) {
                    continue;
                }

                if (!empty($link['id'])) {

                    $youtubeLink = EventYoutubeLink::where('id', $link['id'])
                        ->where('event_id', $event->id)
                        ->first();

                    if ($youtubeLink) {

                        $youtubeLink->update([
                            'url' => $link['url'],
                            'title' => $link['title'] ?? null,
                            'sort_order' => $index + 1,
                        ]);

                        $submittedYoutubeIds[] = $youtubeLink->id;
                    }

                } else {

                    $youtubeLink = EventYoutubeLink::create([
                        'event_id' => $event->id,
                        'url' => $link['url'],
                        'title' => $link['title'] ?? null,
                        'sort_order' => $index + 1,
                    ]);

                    $submittedYoutubeIds[] = $youtubeLink->id;
                }
            }
        }

        $youtubeQuery = EventYoutubeLink::where('event_id', $event->id);

        if (count($submittedYoutubeIds)) {
            $youtubeQuery->whereNotIn('id', $submittedYoutubeIds);
        }

        $youtubeQuery->delete();
        DB::commit();
        if (! empty($oldCertTemplate)) {
            Storage::disk('public')->delete($oldCertTemplate);   // hapus file lama setelah sukses
        }
        return redirect()
            ->route('events.index')
            ->with(
                'success',
                'Data event berhasil diperbarui!'
            );

    } catch (\Throwable $e) {

        DB::rollBack();

        foreach ($newFiles as $file) {

            Storage::disk('public')
                ->delete($file);
        }

        return back()
            ->withInput()
            ->with(
                'error',
                'Gagal memperbarui event: ' . $e->getMessage()
            );
    }
}
/** Mengembalikan path template lama (kalau diganti) supaya dihapus setelah commit. */
private function saveCertificate(Request $request, Event $event, array &$newFiles): ?string
{
    $cert = $event->certificate;

    // Saklar mati: event ini tidak memberi sertifikat
    if (! $request->boolean('has_certificate')) {
        if ($cert) {
            $old = $cert->template;
            $cert->delete();
            return $old;           // file dihapus setelah commit
        }
        return null;
    }

    if (! $request->hasFile('certificate_template') && ! $cert) {
        return null;
    }

    $data = array_filter([
        'name_top'   => $request->input('name_top'),
        'name_size'  => $request->input('name_size'),
        'name_color' => $request->input('name_color'),
    ], fn ($v) => $v !== null);

    $old = null;

    if ($request->hasFile('certificate_template')) {
        $path = $request->file('certificate_template')->store('certificates', 'public');
        $newFiles[] = $path;
        $data['template'] = $path;
        $old = $cert?->template;
    }

    $event->certificate()->updateOrCreate(['event_id' => $event->id], $data);

    return $old;
}
private function canViewAll(): bool
{
    return in_array(ActiveRole::name(), ['Super-Admin', 'Tim']);
}

private function visibleRegistrations(Event $event, bool $canViewAll)
{
    $user = auth()->user();

    return $event->registrations()
        ->with(['user', 'transaction:id,status'])
        ->when(!$canViewAll, function ($q) use ($user) {
            $q->where(function ($w) use ($user) {
                $w->where('event_registrations.user_id', $user->id)
                    ->orWhere(function ($w2) use ($user) {
                        $w2->where('event_registrations.registered_by', $user->id)
                            ->whereHas('transaction', fn ($t) => $t->where('registration_type', 'regular'));
                    });
            });
        });
}
public function show(Event $event)
{
    $event->load('category', 'galleries', 'youtubeLinks');

    $canViewAll = $this->canViewAll();
    abort_unless($canViewAll || $event->is_published, 404);

    $registrations = $this->visibleRegistrations($event, $canViewAll)->latest()->get();
    $counted = $registrations->whereIn('status', ['pending', 'paid', 'attended']);

    $genderSummary = [
        'male'    => $counted->filter(fn ($r) => (int) $r->participant_gender === 1)->count(),
        'female'  => $counted->filter(fn ($r) => (int) $r->participant_gender === 2)->count(),
        'unknown' => $counted->filter(fn ($r) => !in_array((int) $r->participant_gender, [1, 2], true))->count(),
        'pending' => $registrations->where('status', 'pending')->count(),
    ];
    $unknownGender = $counted
        ->filter(fn ($r) => !in_array((int) $r->participant_gender, [1, 2], true))
        ->values();
    $myRegistration = auth()->check()
        ? $event->registrations()
            ->where('user_id', auth()->id())
            ->where('status', '!=', 'cancelled')
            ->orderByRaw("CASE WHEN status = 'attended' THEN 0 ELSE 1 END")
            ->latest('registered_at')
            ->first()
        : null;

    $myRegistration?->setRelation('event', $event);   // hindari query ulang
    $canCert = (bool) $myRegistration?->canDownloadCertificate();
    $alreadyRegistered = $myRegistration !== null;
    return view('events.show', compact('event', 'registrations', 'canViewAll', 'genderSummary', 'unknownGender',
        'myRegistration', 'canCert', 'alreadyRegistered'
    ));
}
public function destroy(Event $event)
{
    $this->authorizePermission('hapus data event');

    // Event yang sudah punya pendaftar berbayar jangan dihapus begitu saja
    $hasPaid = $event->registrations()
        ->whereIn('status', ['paid', 'confirmed', 'attended'])
        ->exists();

    if ($hasPaid) {
        return response()->json([
            'status'  => 'failed',
            'message' => 'Event sudah memiliki peserta yang membayar, tidak bisa dihapus.',
        ], 422);
    }

    $event->delete();

    return response()->json([
        'status'  => 'success',
        'message' => 'Event deleted successfully',
    ]);
}

public function lookupParticipant(Request $request, Event $event)
{
    abort_unless($this->isEventStaff(), 403);

    $validated = $request->validate([
        'ticket_code' => ['required', 'string'],
    ]);

    $registration = EventRegistration::where('event_id', $event->id)
        ->where('ticket_code', $validated['ticket_code'])
        ->with('user:id,fullname,email')
        ->first();

    if (!$registration) {
        return response()->json([
            'success' => false,
            'message' => 'Kode tiket tidak ditemukan untuk event ini.',
        ], 404);
    }

    return response()->json([
        'success' => true,
        'checkin_open' => $event->isCheckinOpen(),
        'participant' => [
            'name'   => $registration->participant_name ?? '-',
            'email'  => $registration->participant_email ?? '-',
            'status' => $registration->status,
        ],
    ]);
}

public function checkinScan(Request $request, Event $event)
{
    abort_unless($this->isEventStaff(), 403);
    if (! $event->isCheckinOpen()) {
        return response()->json([
            'success' => false,
            'message' => 'Check-in baru dibuka pada hari acara ('
                . $event->start_at->translatedFormat('d F Y') . ').',
        ], 422);
    }
    $validated = $request->validate([
        'ticket_code' => ['required', 'string', 'max:50'],
    ]);

    $ticketCode = strtoupper(trim($validated['ticket_code']));

    $registration = EventRegistration::where('event_id', $event->id)
        ->where('ticket_code', $ticketCode)
        ->first();

    if (!$registration) {
        return response()->json([
            'success' => false,
            'message' => 'Kode tiket tidak ditemukan untuk event ini.',
        ], 404);
    }

    $participantData = [
        'name'   => $registration->participant_name ?? '-',
        'email'  => $registration->participant_email ?? '-',
        'status' => $registration->status,
    ];

    if ($registration->status === 'attended') {
        return response()->json([
            'success'     => false,
            'message'     => 'Peserta ini sudah check-in sebelumnya.',
            'participant' => $participantData,
        ], 422);
    }
    if ($registration->status === 'cancelled') {
        return response()->json([
            'success'     => false,
            'message'     => 'Pendaftaran ini sudah dibatalkan (kadaluarsa).',
            'participant' => $participantData,
        ], 422);
    }
    if (!in_array($registration->status, ['paid', 'confirmed'])) {
        return response()->json([
            'success'     => false,
            'message'     => 'Peserta belum menyelesaikan pembayaran/konfirmasi.',
            'participant' => $participantData,
        ], 422);
    }

    // Update bersyarat: kalau dua petugas scan tiket yang sama bersamaan,
    // hanya satu yang berhasil
    $updated = EventRegistration::where('id', $registration->id)
        ->whereIn('status', ['paid', 'confirmed'])
        ->update([
            'status'        => 'attended',
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
        ]);

    if (!$updated) {
        return response()->json([
            'success'     => false,
            'message'     => 'Peserta ini sudah check-in sebelumnya.',
            'participant' => array_merge($participantData, ['status' => 'attended']),
        ], 422);
    }

    return response()->json([
        'success'     => true,
        'message'     => $participantData['name'] . ' berhasil di-check-in.',
        'participant' => array_merge($participantData, ['status' => 'attended']),
    ]);
}
public function showPublic(Event $event)
{
    abort_unless($event->is_published, 404);

    $event->load('category', 'galleries', 'faqs', 'sponsors', 'speakers', 'rundowns');

    $description = Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags($event->description ?? ''))),
        140
    );

    $description = $event->start_at->translatedFormat('d F Y')
        . ($event->location ? ' · ' . $event->location : '')
        . ($description ? ' — ' . $description : '');

    $image = $event->poster
        ? asset('storage/' . $event->poster)
        : null;

    SEOTools::setTitle($event->name);
    SEOTools::setDescription($description);
    SEOTools::setCanonical(url()->current());

    SEOTools::opengraph()->setUrl(url()->current());
    SEOTools::opengraph()->setSiteName('Ruang Kembali');
    SEOTools::opengraph()->addProperty('type', 'website');

    SEOTools::twitter()->setTitle($event->name);
    SEOTools::twitter()->setDescription($description);

    if ($image) {
        SEOTools::opengraph()->addImage($image);
        SEOTools::twitter()->setImage($image);
    }

    return view('events.public-show', compact('event'));
}
public function participantsData(Event $event)
{
    $canViewAll = $this->canViewAll();
    abort_unless($canViewAll || $event->is_published, 404);

    $nameExpr   = 'LOWER(COALESCE(users.fullname, event_registrations.guest_name))';
    $genderExpr = 'CASE WHEN event_registrations.user_id IS NOT NULL
                        THEN users.gender ELSE event_registrations.guest_gender END';

    $query = $this->visibleRegistrations($event, $canViewAll)
        ->leftJoin('users', 'users.id', '=', 'event_registrations.user_id')
        ->select('event_registrations.*');

    return DataTables::eloquent($query)
        ->addIndexColumn()
        ->addColumn('participant', fn ($r) =>
            '<div class="d-flex align-items-center"><span class="avatar me-2"><i class="ti ti-user"></i></span>'
            . '<div class="fw-semibold">' . e($r->participant_name) . '</div></div>')
        ->filterColumn('participant', function ($q, $keyword) use ($nameExpr) {
            $q->whereRaw("$nameExpr LIKE ?", ['%' . mb_strtolower($keyword) . '%']);
        })
        ->orderColumn('participant', "$nameExpr \$1")
        ->addColumn('gender', fn ($r) =>
            in_array((int) $r->participant_gender, [1, 2], true)
                ? e($r->participant_gender_label)
                : '<span class="badge bg-warning"><i class="ti ti-alert-triangle me-1"></i>Belum diisi</span>')
        ->orderColumn('gender', "$genderExpr \$1")
        ->addColumn('payment', fn ($r) => view('events.partials.payment-badge', ['registration' => $r])->render())
        ->orderColumn('payment', 'event_registrations.status $1')
        ->editColumn('ticket_code', function ($r) use ($event, $canViewAll) {
            $url = null;
            if ($canViewAll && in_array($r->status, ['paid', 'confirmed', 'attended'])) {
                $url = URL::temporarySignedRoute(
                    'tickets.public',
                    $event->end_at->copy()->addDay(),
                    ['registration' => $r->id]
                );
            }
            return view('events.partials.ticket-cell', [
                'registration' => $r, 'event' => $event, 'canViewAll' => $canViewAll, 'ticketUrl' => $url,
            ])->render();
        })
        ->addColumn('registered', function ($r) {
            $d = $r->registered_at ?? $r->created_at;
            return $d
                ? $d->translatedFormat('d F Y') . '<div class="text-secondary small">' . $d->format('H:i') . ' WIB</div>'
                : '-';
        })
        ->orderColumn('registered', 'COALESCE(event_registrations.registered_at, event_registrations.created_at) $1')
        ->addColumn('checkin', fn ($r) => $r->status === 'attended'
            ? '<span class="badge bg-success"><i class="ti ti-shield-check me-1"></i>Sudah Check-in</span>'
            : '<span class="badge bg-secondary">Belum Check-in</span>')
        ->orderColumn('checkin', 'event_registrations.status $1')
        ->rawColumns(['participant', 'gender', 'payment', 'ticket_code', 'registered', 'checkin'])
        ->make(true);
}
}