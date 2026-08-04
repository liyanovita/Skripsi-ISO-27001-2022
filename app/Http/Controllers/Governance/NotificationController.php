<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->input('filter');
        $isAdminView = $request->is('admin/*') || $request->routeIs('admin.*') || $user->isAdmin();

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        if ($isAdminView) {
            $baseTaskQuery = \App\Models\AssessmentResult::with(['standard', 'session.organization'])
                ->whereNotNull('treatment_due_date')
                ->whereBetween('maturity_rating', [1, 3]);

            $overdueTasks = (clone $baseTaskQuery)->whereDate('treatment_due_date', '<', now())->get();
            $upcomingTasks = (clone $baseTaskQuery)->whereBetween('treatment_due_date', [now(), now()->addDays(3)])->get();
            $stagnantSessions = \App\Models\AssessmentSession::with('organization')
                ->where('status', '!=', 'completed')
                ->where('updated_at', '<', now()->subDays(7))
                ->get();
        } else {
            $baseTaskQuery = \App\Models\AssessmentResult::with(['standard', 'session'])
                ->whereHas('session', fn($q) => $q->where('user_id', $user->id))
                ->whereNotNull('treatment_due_date')
                ->whereBetween('maturity_rating', [1, 3]);

            $overdueTasks = (clone $baseTaskQuery)->whereDate('treatment_due_date', '<', now())->get();
            $upcomingTasks = (clone $baseTaskQuery)->whereBetween('treatment_due_date', [now(), now()->addDays(3)])->get();
            $stagnantSessions = \App\Models\AssessmentSession::where('user_id', $user->id)
                ->where('status', '!=', 'completed')
                ->where('updated_at', '<', now()->subDays(7))
                ->get();
        }

        $viewData = compact('notifications', 'filter', 'overdueTasks', 'upcomingTasks', 'stagnantSessions');

        if ($isAdminView) {
            return view('admin.notifications.index', $viewData);
        }

        return view('notifications.index', $viewData);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', __('Notification marked as read.'));
    }

    /**
     * Mark a specific notification as unread.
     */
    public function markAsUnread(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->update(['read_at' => null]);

        return redirect()->back()->with('success', __('Notification marked as unread.'));
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', __('All notifications marked as read.'));
    }
}
