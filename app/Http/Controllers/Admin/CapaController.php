<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentResult;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class CapaController extends Controller
{
    public function index(Request $request)
    {
        $query = AssessmentResult::with(['session.user', 'standard'])
            ->where(function ($q) {
                // CAPA is relevant for items with priority risk OR non-compliant OR explicitly scheduled
                $q->where('maturity_rating', '<', 4)
                  ->where('maturity_rating', '>', 0)
                  ->orWhereNotNull('treatment_due_date')
                  ->orWhereNotNull('treatment_status');
            });

        if ($request->filled('status')) {
            if ($request->status == 'pending') {
                $query->whereIn('treatment_status', ['open', 'in_progress'])->orWhereNull('treatment_status');
            } else {
                $query->where('treatment_status', $request->status);
            }
        }

        if ($request->filled('risk')) {
            $query->where('risk_priority', $request->risk);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('standard', function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            })->orWhereHas('session.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $capas = $query->orderByRaw('CASE WHEN treatment_due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('treatment_due_date', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.capa.index', compact('capas'));
    }

    public function edit(AssessmentResult $capa)
    {
        $capa->load(['session.user', 'standard']);
        return view('admin.capa.edit', compact('capa'));
    }

    public function update(Request $request, AssessmentResult $capa)
    {
        $validated = $request->validate([
            'treatment_status' => 'required|in:open,in_progress,completed',
            'treatment_due_date' => 'nullable|date',
            'treatment_pic' => 'nullable|string|max:255',
            'corrective_action_plan_text' => 'required|string',
            'risk_priority' => 'required|in:Low,Medium,High,Critical',
        ]);

        // Audit Trail is handled automatically by AssessmentResult model booted() method,
        // but we can update the attributes.
        // Let's decode or update corrective_action_plan array
        $capa->treatment_status = $validated['treatment_status'];
        $capa->treatment_due_date = $validated['treatment_due_date'];
        $capa->treatment_pic = $validated['treatment_pic'];
        $capa->risk_priority = $validated['risk_priority'];
        
        // Save plan text into corrective_action_plan array
        $plan = $capa->corrective_action_plan ?? [];
        if (!is_array($plan)) {
            $plan = [];
        }
        $plan['action'] = $validated['corrective_action_plan_text'];
        $plan['last_updated_by'] = auth()->user()->name;
        $plan['updated_at'] = now()->toDateTimeString();
        
        $capa->corrective_action_plan = $plan;
        $capa->save();

        return redirect()->route('admin.capa.index')->with('success', 'CAPA Plan updated successfully.');
    }
}
