<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get()->map(function ($cat) {
            $cat->products_count = Product::whereRaw('LOWER(category) = ?', [strtolower($cat->name)])->count();
            return $cat;
        });

        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
            'icon_class' => 'nullable|string|max:80',
            'color_code' => 'nullable|string|size:7',
            'is_active' => 'nullable|boolean',
        ]);

        Category::create([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon_class' => $validated['icon_class'] ?? 'fas fa-folder',
            'color_code' => $validated['color_code'] ?? '#1a6db0',
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'icon_class' => 'nullable|string|max:80',
            'color_code' => 'nullable|string|size:7',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name' => trim($validated['name']),
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'icon_class' => $validated['icon_class'] ?? 'fas fa-folder',
            'color_code' => $validated['color_code'] ?? '#1a6db0',
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }
}
