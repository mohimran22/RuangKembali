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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        EventCategory::create([
            'name' => $request->name,
            'slug' => uniqueSlug($request->name, EventCategory::class),
            'description' => $request->description,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('event_categories.index')
            ->with('success', 'Kategori event berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Piece $piece)
    {
        return view('pieces.show', compact('piece'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventCategory $event_category)
    {
        return view('categories.edit', compact('event_category'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventCategory $event_category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $event_category->update([
            'name' => $request->name,
            'slug' => uniqueSlug($request->name, EventCategory::class, $event_category->id),
            'is_active' => $request->is_active,
            'description' => $request->description,

        ]);

        return redirect()->route('event_categories.index')
            ->with('success', 'Kategori event diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventCategory $event_category)
    {
        $event_category->delete();

        return redirect()->route('event_categories.index')
            ->with('success', 'Kategori event berhasil dihapus.');

    }
}
