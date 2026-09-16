<?php

namespace App\Http\Controllers;

use App\Models\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventcategory = EventCategory::all();
        return view('categories.index', compact('eventcategory'));
    }
    public function create()
    {
         return view('categories.create');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
    ]);

    $slug = $request->filled('slug')
        ? Str::slug($request->slug)
        : Str::slug($request->name);

    // Pastikan slug unik
    $originalSlug = $slug;
    $counter = 2;

    while (EventCategory::where('slug', $slug)->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    EventCategory::create([
        'name' => $request->name,
        'slug' => $slug,
        'description' => $request->description,
        'is_active' => $request->is_active,
    ]);

    return redirect()
        ->route('event_categories.index')
        ->with('success', 'Kategori event berhasil ditambahkan.');
}

    public function show(Piece $piece)
    {
        return view('pieces.show', compact('piece'));
    }

    public function edit(EventCategory $event_category)
    {
        return view('categories.edit', compact('event_category'));
    }

public function update(Request $request, EventCategory $event_category)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
    ]);

    // Gunakan slug dari form jika diisi,
    // jika kosong otomatis dari nama
    $slug = $request->filled('slug')
        ? Str::slug($request->slug)
        : Str::slug($request->name);

    // Pastikan slug unik, tetapi abaikan kategori yang sedang diedit
    $originalSlug = $slug;
    $counter = 2;

    while (
        EventCategory::where('slug', $slug)
            ->where('id', '!=', $event_category->id)
            ->exists()
    ) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $event_category->update([
        'name' => $request->name,
        'slug' => $slug,
        'is_active' => $request->is_active,
        'description' => $request->description,
    ]);

    return redirect()
        ->route('event_categories.index')
        ->with('success', 'Kategori event diperbarui.');
}

public function destroy(EventCategory $event_category)
{
    if ($event_category->events()->exists()) {
        return redirect()
            ->route('event_categories.index')
            ->with('error', 'Kategori event tidak dapat dihapus karena masih digunakan oleh event.');
    }

    $event_category->delete();

    return redirect()
        ->route('event_categories.index')
        ->with('success', 'Kategori event berhasil dihapus.');
}
}
