<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $orderby = in_array($request->query('orderby'), ['name', 'articles_count']) ? $request->query('orderby') : 'name';
        $order   = $request->query('order') === 'desc' ? 'desc' : 'asc';

        $cats = Category::withCount('articles')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->orderBy($orderby, $order)
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('cats', 'orderby', 'order'));
    }

public function store(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:100',
        'slug' => 'nullable|string|max:100',
        'parent_id' => 'nullable|integer|exists:categories,id',
    ]);

    $name = trim(preg_replace('/\s+/', ' ', $data['name']));
    $parentId = $data['parent_id'] ?? null;

    // Cegah kategori dengan nama sama pada induk yang sama
    $existing = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->where('parent_id', $parentId)
        ->first();

    if ($existing) {
        return response()->json(
            $this->payload($existing, true)
        );
    }

    $category = Category::create([
        'name' => $name,
        'slug' => $data['slug'] ?? null,
        'parent_id' => $parentId,
    ]);

    return response()->json(
        $this->payload($category, false),
        201
    );
}

private function payload(Category $category, bool $existing): array
{
    return [
        'id' => $category->id,
        'name' => $category->name,
        'slug' => $category->slug,
        'parent_id' => $category->parent_id,
        'existing' => $existing,
    ];
}

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $cat->id,
            'slug' => 'nullable|string|max:100|unique:categories,slug,' . $cat->id,
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Kategori diperbarui.');
    }

    public function destroy(Category $cat)
    {
        $cat->articles()->detach();
        $cat->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori dihapus.');
    }

    public function bulk(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if ($request->input('bulk_action') === 'delete' && $ids) {
            foreach (Category::whereIn('id', $ids)->get() as $cat) {
                $cat->articles()->detach();
                $cat->delete();
            }
            return redirect()->route('categories.index')->with('success', count($ids) . ' Kategori dihapus.');
        }

        return redirect()->route('categories.index');
    }
}