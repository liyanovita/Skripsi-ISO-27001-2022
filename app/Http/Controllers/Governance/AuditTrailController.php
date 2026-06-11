<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\AssessmentSession;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Tampilkan history log perubahan (Audit Trail)
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        
        $sessions = AssessmentSession::where('user_id', $userId)->orderByDesc('created_at')->get();
        $selectedId = $request->get('session_id', $sessions->first()?->id);
        $search = $request->get('search');
        
        $query = $this->buildQuery($userId, $selectedId, $search);
        
        $trails = $query->simplePaginate(15)->withQueryString();

        return view('pages.audit-trail.index', compact('trails', 'sessions', 'selectedId', 'search'));
    }

    public function export(Request $request)
    {
        $userId = auth()->id();
        $selectedId = $request->get('session_id');
        $search = $request->get('search');

        $query = $this->buildQuery($userId, $selectedId, $search);
        $trails = $query->get();

        $fileName = 'Audit_Trail_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date & Time', 'User', 'Control Code', 'Field Changed', 'Old Value', 'New Value'];

        $callback = function() use($trails, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($trails as $trail) {
                $row['Date & Time']  = $trail->created_at->format('Y-m-d H:i:s');
                $row['User']         = $trail->user->name ?? 'System';
                $row['Control Code'] = $trail->model?->standard?->code ?? 'N/A';
                $row['Field Changed']= $trail->field_changed;
                $row['Old Value']    = $trail->old_value;
                $row['New Value']    = $trail->new_value;

                fputcsv($file, [$row['Date & Time'], $row['User'], $row['Control Code'], $row['Field Changed'], $row['Old Value'], $row['New Value']]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery($userId, $selectedId, $search)
    {
        $query = AuditTrail::with(['user', 'model' => function ($morphTo) {
            $morphTo->morphWith([\App\Models\AssessmentResult::class => ['standard']]);
        }])->orderByDesc('created_at');

        if ($selectedId) {
            $query->where('model_type', \App\Models\AssessmentResult::class)
                ->whereHasMorph('model', [\App\Models\AssessmentResult::class], function ($q) use ($selectedId) {
                    $q->where('session_id', $selectedId);
                });
        } else {
            $query->where('user_id', $userId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('field_changed', 'like', "%{$search}%")
                  ->orWhere('old_value', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHasMorph('model', [\App\Models\AssessmentResult::class], function ($q2) use ($search) {
                      $q2->whereHas('standard', function($q3) use ($search) {
                          $q3->where('code', 'like', "%{$search}%");
                      });
                  });
            });
        }

        return $query;
    }
}
