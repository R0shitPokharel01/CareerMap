<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
    public function send(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'notification_type' => 'required|string',
            'recipient' => 'required|in:all,user',
            'user_id' => 'nullable|exists:users,id',
        ]);


        if ($validated['recipient'] === 'user') {
            $users = User::where('id', $validated['user_id'])->get();
        } else {
            $users = User::where('role', 'user')->get();
        }


        Notification::send(
            $users,
            new AdminMessageNotification(
                $validated['title'],
                $validated['message'],
                $validated['notification_type'],
                $admin->id
            )
        );


        return response()->json([
            'message' => 'Notification sent successfully',
            'sent_to' => $users->count()
        ]);
    }
}
