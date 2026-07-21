<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $totalOrganizations = Organization::count();
        $totalSessions = Organization::withCount('sessions')->get()->sum('sessions_count');

        $organizations = Organization::query()
            ->withCount('sessions')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('contact_email', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.organizations.index', compact(
            'organizations',
            'search',
            'totalOrganizations',
            'totalSessions'
        ));
    }

    public function create()
    {
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:organizations,code',
            'description' => 'nullable|string',
            'business_sector' => 'nullable|string|max:255',
            'organization_scale' => 'nullable|string|max:255',
            'it_governance_structure' => 'nullable|string',
            'isms_scope' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        Organization::create($validated);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $organization->loadCount('sessions');

        // Sessions associated with this organization
        $sessions = $organization->sessions()
            ->with(['user', 'invitedUsers'])
            ->withCount('results')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Stats
        $stats = [
            'total_sessions'     => $sessions->count(),
            'active_sessions'    => $sessions->where('status', 'in_progress')->count(),
            'completed_sessions' => $sessions->where('status', 'completed')->count(),
            'avg_maturity'       => $sessions->whereNotNull('overall_maturity_score')->avg('overall_maturity_score') ?? 0,
        ];

        return view('admin.organizations.show', compact('organization', 'sessions', 'stats'));
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('organizations', 'code')->ignore($organization->id),
            ],
            'description' => 'nullable|string',
            'business_sector' => 'nullable|string|max:255',
            'organization_scale' => 'nullable|string|max:255',
            'it_governance_structure' => 'nullable|string',
            'isms_scope' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        $organization->update($validated);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}
