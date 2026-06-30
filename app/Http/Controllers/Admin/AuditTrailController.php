<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        // Stats
        $totalLogs    = AuditTrail::count();
        $logsToday    = AuditTrail::whereDate('created_at', today())->count();
        $activeUsers  = AuditTrail::whereNotNull('user_id')->distinct('user_id')->count('user_id');

        $query = AuditTrail::with(['user']);

        if ($request->filled('user_id')) {
            $query->forUser($request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('field_changed', 'like', "%{$search}%")
                  ->orWhere('old_value', 'like', "%{$search}%")
                  ->orWhere('new_value', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        return view('admin.logs.index', compact('logs', 'users', 'totalLogs', 'logsToday', 'activeUsers'));
    }

    public function exportCsv()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=audit_trail_' . date('Y-m-d') . '.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $logs = AuditTrail::with('user')->orderByDesc('created_at')->get();

        $booleanFields = ['is_applicable'];
        $rows = [];

        foreach ($logs as $log) {
            $isBool     = in_array($log->field_changed, $booleanFields);
            $oldValue   = $log->old_value ?? null;
            $newValue   = $log->new_value ?? null;
            $oldDisplay = !is_null($oldValue) ? ($isBool ? ($oldValue == '1' ? 'Yes' : 'No') : $oldValue) : '-';
            $newDisplay = !is_null($newValue) ? ($isBool ? ($newValue == '1' ? 'Yes' : 'No') : $newValue) : '-';

            $rows[] = [
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user?->name ?? 'System',
                ucfirst($log->action),
                class_basename($log->model_type),
                $log->model_id,
                $log->field_changed,
                $oldDisplay,
                $newDisplay,
            ];
        }

        $filename = 'audit_trail_' . date('Y-m-d') . '.xlsx';
        return ExcelExportService::download($filename, ['Timestamp', 'User', 'Action', 'Model', 'Model ID', 'Field Changed', 'Old Value', 'New Value'], $rows, 'System Logs');
    }
}
