<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('services')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $originalSlug = $validated['slug'];
        $counter      = 1;
        while (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        Category::create($validated);

        // Invalidate category cache
        Cache::forget('categories_all');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique (excluding current category)
        $originalSlug = $validated['slug'];
        $counter      = 1;
        while (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
            $validated['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $category->update($validated);

        // Invalidate category cache
        Cache::forget('categories_all');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has services
        if ($category->services()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing services. Please reassign or delete the services first.');
        }

        $category->delete();

        // Invalidate category cache
        Cache::forget('categories_all');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
