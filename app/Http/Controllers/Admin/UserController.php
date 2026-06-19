<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search       = $request->input('search');
        $roleFilter   = $request->input('role');
        $statusFilter = $request->input('status');

        // KPI stats
        $totalUsers  = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $adminCount  = User::where('role', 'admin')->count();

        $users = User::query()
            ->withCount('assessmentSessions')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('organization_name', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, fn($q) => $q->where('role', $roleFilter))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact(
            'users', 'search', 'roleFilter', 'statusFilter',
            'totalUsers', 'activeUsers', 'adminCount'
        ));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,suspended',
            'organization_name' => 'nullable|string|max:255',
            'business_sector' => 'nullable|string|max:255',
            'organization_scale' => 'nullable|string|max:255',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->loadCount(['assessmentSessions', 'communityTemplates', 'auditTrails']);
        $sessions = $user->assessmentSessions()
            ->withCount('results')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.users.show', compact('user', 'sessions'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,suspended',
            'organization_name' => 'nullable|string|max:255',
            'business_sector' => 'nullable|string|max:255',
            'organization_scale' => 'nullable|string|max:255',
        ]);

        // Prevent demoting yourself
        if ($user->id === auth()->id() && $validated['role'] !== 'admin') {
            return back()->with('error', 'You cannot demote yourself from admin.');
        }

        $user->update($validated);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('success', "Password for {$user->name} has been reset.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend yourself.');
        }

        $user->status = $user->status === 'active' ? 'suspended' : 'active';
        $user->save();

        return back()->with('success', "User status updated to {$user->status}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
