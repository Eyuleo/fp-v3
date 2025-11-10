<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::with(['student', 'category'])
            ->where('is_active', true);

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Filter by delivery time
        if ($request->filled('max_delivery_days')) {
            $query->where('delivery_days', '<=', $request->input('max_delivery_days'));
        }

        // Filter by minimum rating
        if ($request->filled('min_rating')) {
            $minRating = $request->input('min_rating');
            $query->whereHas('student', function ($q) use ($minRating) {
                $q->whereHas('reviewsReceived', function ($q) use ($minRating) {
                    $q->havingRaw('AVG(rating) >= ?', [$minRating]);
                });
            });
        }

        // Sort options
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                // This is complex, we'll sort by student rating
                $query->withAvg('student.reviewsReceived as student_rating', 'rating')
                    ->orderByDesc('student_rating');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $services   = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('services.index', compact('services', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Service::class);

        $categories = Category::orderBy('name')->get();

        return view('services.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'category_id'   => 'required|exists:categories,id',
            'tags'          => 'nullable|string',
            'price'         => 'required|numeric|min:0|max:99999999.99',
            'delivery_days' => 'required|integer|min:1|max:365',
            'sample_work'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        // Generate unique slug
        $slug = $this->generateUniqueSlug($validated['title']);

        // Handle sample work upload
        $sampleWorkPath = null;
        if ($request->hasFile('sample_work')) {
            $sampleWorkPath = $request->file('sample_work')->store('sample_works', 'private');
        }

        // Parse tags
        $tags = $this->parseTags($request->input('tags'));

        $service = Service::create([
            'student_id'       => auth()->id(),
            'title'            => $validated['title'],
            'slug'             => $slug,
            'description'      => $validated['description'],
            'category_id'      => $validated['category_id'],
            'tags'             => $tags,
            'price'            => $validated['price'],
            'delivery_days'    => $validated['delivery_days'],
            'sample_work_path' => $sampleWorkPath,
            'is_active'        => true,
        ]);

        return redirect()->route('services.show', $service->slug)
            ->with('success', 'Service created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        $service = Service::with(['student', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();

        $this->authorize('view', $service);

        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $service);

        $categories = Category::orderBy('name')->get();

        return view('services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $service);

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'category_id'   => 'required|exists:categories,id',
            'tags'          => 'nullable|string',
            'price'         => 'required|numeric|min:0|max:99999999.99',
            'delivery_days' => 'required|integer|min:1|max:365',
            'sample_work'   => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,zip|max:10240',
        ]);

        // Generate new slug if title changed
        if ($validated['title'] !== $service->title) {
            $newSlug       = $this->generateUniqueSlug($validated['title'], $service->getKey());
            $service->slug = $newSlug;
        }

        // Handle sample work upload
        if ($request->hasFile('sample_work')) {
            // Delete old file
            if ($service->sample_work_path) {
                Storage::disk('private')->delete($service->sample_work_path);
            }
            $service->sample_work_path = $request->file('sample_work')->store('sample_works', 'private');
        }

        // Parse tags
        $tags = $this->parseTags($request->input('tags'));

        $service->update([
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'category_id'   => $validated['category_id'],
            'tags'          => $tags,
            'price'         => $validated['price'],
            'delivery_days' => $validated['delivery_days'],
        ]);

        return redirect()->route('services.show', $service->slug)
            ->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        $this->authorize('delete', $service);

        // Delete sample work file
        if ($service->sample_work_path) {
            Storage::disk('private')->delete($service->sample_work_path);
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully!');
    }

    /**
     * Toggle service activation status.
     */
    public function toggleActive(string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        $this->authorize('update', $service);

        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        $status = $service->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Service {$status} successfully!");
    }

    /**
     * Generate a unique slug from title.
     */
    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug         = Str::slug($title);
        $originalSlug = $slug;
        $counter      = 1;

        while (true) {
            $query = Service::where('slug', $slug);

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            if (! $query->exists()) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Parse tags from comma-separated string.
     */
    protected function parseTags(?string $tagsString): ?array
    {
        if (empty($tagsString)) {
            return null;
        }

        $tags = array_map('trim', explode(',', $tagsString));
        return array_filter($tags);
    }

    /**
     * Serve sample work file (for images).
     */
    public function serveSampleWork(string $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        if (! $service->sample_work_path || ! Storage::disk('private')->exists($service->sample_work_path)) {
            abort(404);
        }

        // Only serve images publicly
        if (! $service->sampleWorkIsImage()) {
            abort(403, 'Only images can be displayed publicly');
        }

        return Storage::disk('private')->response($service->sample_work_path);
    }

    /**
     * Display student's own services (without search/filters).
     */
    public function myServices()
    {
        $services = Service::where('student_id', auth()->id())
            ->with(['category'])
            ->withCount('orders')
            ->withAvg('reviews', 'rating')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('student.services', compact('services'));
    }
}
