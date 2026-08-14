<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use App\Models\EventGallery;
use App\Models\Customer;
use App\Models\Affiliator;
use App\Models\Worker;
use App\Models\Invoice;
use App\Models\InvoiceBuild;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\SubDistrict;
use App\Models\PostalCode;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
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
                $url = route('events.show', $event->id);

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

                if (auth()->user()->can('lihat data event')) {
                    $buttons .= '
                        <a href="' . route('events.show', $event->id) . '"
                           class="btn btn-icon btn-sm btn-primary"
                           title="Detail">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';
                }

                if (auth()->user()->can('ubah data event')) {
                    $buttons .= '
                        <a href="' . route('events.edit', $event->id) . '"
                           class="btn btn-icon btn-sm btn-warning"
                           title="Edit">
                            <i class="ti ti-edit"></i>
                        </a>
                    ';
                }

                if (auth()->user()->can('hapus data event')) {
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

    return view('events.create', compact(
        'categories',
        'eventTypes',
        'audiences',
        'statuses'
    ));
}

public function store(Request $request)
{
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
        'quota' => 'nullable|integer|min:1',
        'description' => 'nullable|string',
        'is_published' => 'required|boolean',
        'poster' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'youtube_url' => 'nullable|url|max:500',
        'gallery_images' => 'nullable|array',
        'gallery_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
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
            'price' => $request->event_type === 'free'
                ? 0
                : ($request->price ?? 0),
            'quota' => $request->quota,
            'poster' => $poster,
            'thumbnail' => $thumbnail,
            'youtube_url' => $request->youtube_url,
            'description' => $request->description,
            'is_published' => $request->boolean('is_published'),
        ]);
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
    $prefix = 'EVT' . now()->format('Ym');

    $last = Event::where('event_code', 'like', $prefix . '%')
        ->latest()
        ->first();

    if (!$last) {
        return $prefix . '0001';
    }

    $number = (int) substr($last->event_code, -4) + 1;

    return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
}
public function edit(Event $event)
{
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

    return view('events.edit', compact(
        'event',
        'categories',
        'eventTypes',
        'audiences',
        'statuses'
    ));
}

public function update(Request $request, Event $event)
{
    abort_if(
        auth()->user()->cannot('lihat daftar event'),
        403
    );

    $request->validate([

        // =========================
        // EVENT
        // =========================
        'name' => 'required|string|max:255',

        'event_category_id' =>
            'required|exists:event_categories,id',

        'event_type' =>
            'required|in:free,paid',

        'audience_type' =>
            'required|in:public,gender,age',

        'registration_open' =>
            'nullable|date',

        'registration_close' =>
            'nullable|date|after_or_equal:registration_open',

        'start_at' =>
            'required|date',

        'end_at' =>
            'required|date|after:start_at',

        'location' =>
            'nullable|string|max:255',

        'price' =>
            'nullable|numeric|min:0',

        'quota' =>
            'nullable|integer|min:1',

        'is_published' =>
            'required|boolean',

        'description' =>
            'nullable|string',

        // =========================
        // MEDIA
        // =========================
        'poster' =>
            'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        'thumbnail' =>
            'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

        'youtube_url' =>
            'nullable|url|max:500',

        // =========================
        // GALLERY
        // =========================
        'gallery_images' =>
            'nullable|array',

        'gallery_images.*' =>
            'image|mimes:jpg,jpeg,png,webp|max:2048',

        'delete_gallery_ids' =>
            'nullable|array',

        'delete_gallery_ids.*' =>
            'exists:event_galleries,id',

        'gallery_captions' =>
            'nullable|array',

        'gallery_captions.*' =>
            'nullable|string|max:255',
    ]);

    DB::beginTransaction();

    $newFiles = [];

    try {

        $eventData = [
            'name' => $request->name,
            'event_category_id' => $request->event_category_id,
            'event_type' => $request->event_type,
            'audience_type' => $request->audience_type,

            'registration_open' =>
                $request->registration_open,

            'registration_close' =>
                $request->registration_close,

            'start_at' =>
                $request->start_at,

            'end_at' =>
                $request->end_at,

            'location' =>
                $request->location,

            'price' => $request->event_type === 'free'
                ? 0
                : ($request->price ?? 0),

            'quota' =>
                $request->quota,

            'youtube_url' =>
                $request->youtube_url,

            'description' =>
                $request->description,

            'is_published' =>
                $request->boolean('is_published'),
        ];


        if ($request->hasFile('poster')) {

            $oldPoster = $event->poster;

            $poster = $request->file('poster')
                ->store('events/posters', 'public');

            $newFiles[] = $poster;

            $eventData['poster'] = $poster;

            if ($oldPoster) {
                Storage::disk('public')
                    ->delete($oldPoster);
            }
        }

        if ($request->hasFile('thumbnail')) {

            $oldThumbnail = $event->thumbnail;

            $thumbnail = $request->file('thumbnail')
                ->store('events/thumbnails', 'public');

            $newFiles[] = $thumbnail;

            $eventData['thumbnail'] = $thumbnail;

            /*
             * Hapus thumbnail lama.
             */
            if ($oldThumbnail) {
                Storage::disk('public')
                    ->delete($oldThumbnail);
            }
        }

        $event->update($eventData);

        if ($request->filled('delete_gallery_ids')) {

            $galleries = EventGallery::where('event_id', $event->id)
                ->whereIn(
                    'id',
                    $request->delete_gallery_ids
                )
                ->get();

            foreach ($galleries as $gallery) {

                /*
                 * Hapus file gallery
                 */
                if ($gallery->image) {

                    Storage::disk('public')
                        ->delete($gallery->image);
                }

                /*
                 * Hapus record
                 */
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

        DB::commit();

        return redirect()
            ->route('events.index')
            ->with(
                'success',
                'Data event berhasil diperbarui!'
            );

    } catch (\Throwable $e) {

        DB::rollBack();

        /*
         * Hapus file-file baru yang sudah berhasil
         * di-upload tetapi proses update gagal.
         */
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
public function show($id)
{
    $event = Event::with('category', 'galleries')
        ->findOrFail($id);

    return view('events.show', compact('event'));
}
     public function destroy(Event $event) 
    {
        if ($event) {
            $event->delete();
            return response()->json(['status' => 'success', 'message' => 'Event deleted successfully']);
        }

        return response()->json(['status' => 'failed', 'message' => 'Unable to delete']);
    }

}