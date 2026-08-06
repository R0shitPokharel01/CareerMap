<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\UserProgressController;
use App\Http\Controllers\User\UserAchievementsController as UserAchievementsController;
use App\Services\DashboardServices\DashboardService;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardService $dashboardService,
        UserProgressController $progressController,
        UserAchievementsController $userAchievementsController,
        NotificationController $notificationController
    ) {
        return response()->json(
            $dashboardService->index($request, $progressController, $userAchievementsController, $notificationController)
        );
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|image|max:2048', // Optional avatar upload
        ]);

        // Update user profile
        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ]);
    }
}