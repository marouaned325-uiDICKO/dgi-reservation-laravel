<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('date_env')
            ->get();

        Notification::where('user_id', $request->user()->id)
            ->where('notif_lu', false)
            ->update(['notif_lu' => true]);

        return view('notifications.index', compact('notifications'));
    }
}