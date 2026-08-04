<?php

namespace App\Services\DashboardServices;

use App\Http\Controllers\User\UserProgressController;
use App\Http\Controllers\User\UserAchievementsController;
use App\Http\Controllers\NotificationController;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardService
{
    private function dailyTip(): string
    {
        $tips = [

            'Work smarter, not harder. Prioritize your tasks and focus on what truly matters.',

            'Stay organized and plan your day ahead.',

            'Take breaks to recharge your mind.',

            'Embrace continuous learning.',

            'Set clear goals and track your progress.',

            'Practice mindfulness and meditation.',

            'Collaborate and communicate effectively.',

            'Stay positive and resilient.'

        ];

        return $tips[array_rand($tips)];
    }

    public function index(
        Request $request,
        UserProgressController $progressController,
        UserAchievementsController $userAchievementsController,
        NotificationController $notificationController
    ) {

        return [
            'message' => 'Dashboard data fetched successfully',

            'user' => $request->user(),

            'summary' => $progressController->summary()->getData(true),

            'roadmaps' => $progressController->allRoadmaps()->getData(true),

            'tasks' => [],

            'achievements' => $userAchievementsController->earned()->getData(true),

            'notifications' => [],

            'daily_tip' => $this->dailyTip(),
        ];
    }
}