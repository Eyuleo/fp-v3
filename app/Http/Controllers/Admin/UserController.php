<?php
namespace App\Http\Controllers\Admin;

use App\Actions\ReinstateUserAction;
use App\Actions\SuspendUserAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Filter by registration date
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['services', 'ordersAsStudent', 'ordersAsClient', 'reviews']);

        return view('admin.users.show', compact('user'));
    }

    public function suspend(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Cannot suspend admin users.');
        }

        app(SuspendUserAction::class)->execute($user);

        return back()->with('success', 'User has been suspended successfully.');
    }

    public function reinstate(User $user)
    {
        app(ReinstateUserAction::class)->execute($user);

        return back()->with('success', 'User has been reinstated successfully.');
    }
}
