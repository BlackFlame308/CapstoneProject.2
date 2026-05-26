<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationManagementController extends Controller
{
    /**
     * Display notification dashboard with statistics
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'channel' => $request->get('channel'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ];

        $query = Notification::query();

        // Apply filters
        if ($filters['status']) {
            $query->where('notification_status', $filters['status']);
        }
        if ($filters['channel']) {
            $query->where('notification_channel', $filters['channel']);
        }
        if ($filters['start_date']) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if ($filters['end_date']) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $notifications = $query->with('user')
            ->latest()
            ->paginate(20);

        // Statistics
        $totalNotifications = Notification::count();
        $sentCount = Notification::where('notification_status', 'sent')->count();
        $failedCount = Notification::where('notification_status', 'failed')->count();
        $pendingCount = Notification::where('notification_status', 'pending')->count();

        // Channel breakdown
        $byChannel = Notification::groupBy('notification_channel')
            ->selectRaw('notification_channel, COUNT(*) as count')
            ->pluck('count', 'notification_channel');

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'filters' => $filters,
            'totalNotifications' => $totalNotifications,
            'sentCount' => $sentCount,
            'failedCount' => $failedCount,
            'pendingCount' => $pendingCount,
            'byChannel' => $byChannel,
        ]);
    }

    /**
     * Show notification detail
     */
    public function show(Notification $notification)
    {
        $notification->load('user');
        return view('admin.notifications.show', ['notification' => $notification]);
    }

    /**
     * Create notification form
     */
    public function create()
    {
        $users = User::where('is_active', true)->pluck('name', 'id');
        $roles = Role::orderBy('name')->get();
        $channels = ['email', 'sms', 'push', 'in-app'];
        $severities = ['low', 'medium', 'high', 'critical'];

        return view('admin.notifications.create', [
            'users' => $users,
            'roles' => $roles,
            'channels' => $channels,
            'severities' => $severities,
        ]);
    }

    /**
     * Store new notification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:user,role,all',
            'user_id' => 'nullable|required_if:recipient_type,user|exists:users,id',
            'role_id' => 'nullable|required_if:recipient_type,role|exists:roles,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'notification_channel' => 'required|in:email,sms,push,in-app',
            'severity_level' => 'required|in:low,medium,high,critical',
        ]);

        try {
            // Determine recipients
            $recipients = [];
            if ($validated['recipient_type'] === 'user') {
                $recipients = [User::find($validated['user_id'])];
            } elseif ($validated['recipient_type'] === 'role') {
                $recipients = User::where('role_id', $validated['role_id'])->get();
            } else {
                $recipients = User::all();
            }

            // Create notifications for each recipient
            foreach ($recipients as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'notification_channel' => $validated['notification_channel'],
                    'severity_level' => $validated['severity_level'],
                    'notification_status' => 'pending',
                ]);
            }

            return redirect()->route('admin.notifications.index')
                ->with('success', count($recipients) . ' notification(s) created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * Retry failed notification
     */
    public function retry(Notification $notification)
    {
        try {
            $notification->update(['notification_status' => 'pending']);
            // TODO: Implement notification sending logic
            return back()->with('success', 'Notification queued for retry');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to retry notification: ' . $e->getMessage());
        }
    }

    /**
     * Delete notification
     */
    public function destroy(Notification $notification)
    {
        try {
            $notification->delete();
            return back()->with('success', 'Notification deleted');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete notification: ' . $e->getMessage());
        }
    }

    /**
     * Send test notification
     */
    public function sendTest(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'channel' => 'required|in:email,sms,push,in-app',
        ]);

        try {
            // Create and send test notification
            $notification = Notification::create([
                'user_id' => $validated['user_id'],
                'title' => 'Test Notification',
                'message' => 'This is a test notification from the system.',
                'notification_channel' => $validated['channel'],
                'severity_level' => 'low',
                'notification_status' => 'pending',
            ]);

            // TODO: Implement actual sending logic based on channel

            return back()->with('success', 'Test notification sent to user');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test notification: ' . $e->getMessage());
        }
    }
}
