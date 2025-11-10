<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Notifications\ServiceDisabledNotification;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['student', 'category']);

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $services   = $query->orderBy('created_at', 'desc')->paginate(20);
        $categories = \App\Models\Category::all();

        return view('admin.services.index', compact('services', 'categories'));
    }

    public function approve(Service $service)
    {
        $service->update(['is_active' => true]);

        return back()->with('success', 'Service has been approved and is now active.');
    }

    public function disable(Request $request, Service $service)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $service->update(['is_active' => false]);

        // Notify the student
        $service->student->notify(new ServiceDisabledNotification($service, $request->reason));

        return back()->with('success', 'Service has been disabled and the student has been notified.');
    }
}
