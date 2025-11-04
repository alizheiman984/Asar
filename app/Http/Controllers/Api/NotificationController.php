<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth()->user();

        $unreadCount = $employee->unreadNotifications()->count();

        $notifications = $employee->notifications()->latest()->get();

        $employee->unreadNotifications->markAsRead();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

}
