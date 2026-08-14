<?php

namespace App\Http\Controllers;

use App\Models\EventSpeaker;
use Illuminate\Http\Request;

class EventSpeakerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $event_speakers = EventSpeaker::all();
        return view('speakers.index', compact('event_speakers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('speakers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'factory_origin' => 'nullable|string|max:255',
        ]);

        ProductBrand::create([
            'name' => $request->name,
            'factory_origin' => $request->factory_origin,
        ]);

        return redirect()->route('product_speakers.index')
            ->with('success', 'Piece berhasil ditambahkan.');
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
    public function edit(ProductBrand $product_brand)
    {
        return view('speakers.edit', compact('product_brand'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductBrand $product_brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'factory_origin' => 'nullable|string|max:255',
        ]);

        $product_brand->update([
            'name' => $request->name,
            'factory_origin' => $request->factory_origin,

        ]);

        return redirect()->route('product_speakers.index')
            ->with('success', 'Piece berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBrand $product_brand)
    {
        $product_brand->delete();

        return redirect()->route('product_speakers.index')
            ->with('success', 'Piece berhasil dihapus.');

    }
}
