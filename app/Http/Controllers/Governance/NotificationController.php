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

        $query = $user->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate(15)->withQueryString();

        if ($request->is('admin/*') || $request->routeIs('admin.*')) {
            return view('admin.notifications.index', compact('notifications', 'filter'));
        }

        return view('notifications.index', compact('notifications', 'filter'));
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
