<?php

namespace App\Services\DashboardServices;

use App\Http\Controllers\User\UserProgressController;
use App\Http\Controllers\User\UserAchievementsController;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardService
{
    public function index(
        Request $request,
        UserProgressController $progressController,
        UserAchievementsController $userAchievementsController
    ) {

        return [
            'message' => 'Dashboard data fetched successfully',
            "user" => User::find($request->user()->id),
            "summary" => $progressController->summary(),
            "roadmaps" => $progressController->allRoadmaps(),
            "tasks" => [],
            "achievements" => $userAchievementsController->earned(),
            "notifications" => [],
            "daily_tip" => [
                'Work smarter, not harder. Prioritize your tasks and focus on what truly matters.',
                '"Stay organized and plan your day ahead. A well-structured schedule can boost productivity and reduce stress.',
                '"Take breaks to recharge your mind. Short breaks during work can improve focus and creativity.',
                "Embrace continuous learning. Keep updating your skills and knowledge to stay ahead in your career.",
                "Set clear goals and track your progress. This will help you stay motivated and achieve more in less time.",
                "Practice mindfulness and meditation. A calm mind can enhance decision-making and problem-solving abilities.",
                "Collaborate and communicate effectively. Teamwork can lead to innovative solutions and better outcomes.",
                "Stay positive and resilient. Challenges are opportunities for growth and learning.",
            ],


        ];
    }
}