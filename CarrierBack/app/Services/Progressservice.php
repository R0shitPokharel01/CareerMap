<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRoadmapProgress;
use App\Models\UserTaskProgress;
use App\Models\UserStreak;
use App\Models\UserAchievement;
use Carbon\Carbon;

//ProgressService Handles all progress tracking for roadmaps, tasks, and streaks.
//After updating progress, it calls AchievementService to check
//if any new achievements should be awarded automatically.

class ProgressService
{
    public function __construct(
        private AchievementService $achievementService
    ) {}

    //Task Progress 
    //Mark a task as completed.
    //Also recalculates the parent roadmap's overall progress.
    
    public function completeTask(User $user, int $taskId, int $roadmapId): UserTaskProgress
    {
        $task = UserTaskProgress::updateOrCreate(
            ['user_id' => $user->id, 'task_id' => $taskId],
            [
                'roadmap_id'   => $roadmapId,
                'status'       => 'completed',
                'completed_at' => now(),
            ]
        );

        // Recalculate roadmap progress after task is completed
        $this->recalculateRoadmapProgress($user, $roadmapId);

        // Update streak and check achievements
        $this->updateStreak($user);
        $this->achievementService->checkAndAward($user);

        return $task;
    }

    // Mark a task as in_progress (user has started it).
     
    public function startTask(User $user, int $taskId, int $roadmapId): UserTaskProgress
    {
        $task = UserTaskProgress::updateOrCreate(
            ['user_id' => $user->id, 'task_id' => $taskId],
            [
                'roadmap_id' => $roadmapId,
                'status'     => 'in_progress',
            ]
        );

        // Make sure the roadmap is marked as in_progress
        UserRoadmapProgress::updateOrCreate(
            ['user_id' => $user->id, 'roadmap_id' => $roadmapId],
            [
                'status'     => 'in_progress',
                'started_at' => fn($p) => $p->started_at ?? now(),
            ]
        );

        return $task;
    }

    // Roadmap Progress Recalculate a roadmap's overall percent_complete based on how many tasks the user has completed.Called automatically after completeTask().
    public function recalculateRoadmapProgress(User $user, int $roadmapId): UserRoadmapProgress
    {
        $total = UserTaskProgress::where('user_id', $user->id)
                                 ->where('roadmap_id', $roadmapId)
                                 ->count();

        $completed = UserTaskProgress::where('user_id', $user->id)
                                     ->where('roadmap_id', $roadmapId)
                                    ->where('status', 'completed')
                                     ->count();

        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $status = match (true) {
            $percent === 0   => 'not_started',
            $percent === 100 => 'completed',
            default          => 'in_progress',
        };

        $progress = UserRoadmapProgress::updateOrCreate(
            ['user_id' => $user->id, 'roadmap_id' => $roadmapId],
            [
                'percent_complete' => $percent,
                'status'           => $status,
                'completed_at'     => $status === 'completed' ? now() : null,
            ]
        );

        return $progress;
    }

    //Streak 
    //Update the user's daily activity streak.Call this whenever the user does any meaningful action.
    public function updateStreak(User $user): UserStreak
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $today     = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();
        $lastActive = $streak->last_active_date?->toDateString();

        //Already counted today  no change needed
        if ($lastActive === $today) {
            return $streak;
        }

        if ($lastActive === $yesterday) {
            // Continued the streak
            $streak->current_streak += 1;
        } else {
            // Streak broken or first time
            $streak->current_streak = 1;
        }

        $streak->longest_streak  = max($streak->longest_streak, $streak->current_streak);
        $streak->last_active_date = $today;
        $streak->save();

        return $streak;
    }

    //Profile Summary 
    //For Returning a complete progress summary for the user's profile page.This powers the Profile / Progress Tracking section.
    public function getProfileSummary(User $user): array
    {
        $roadmaps = UserRoadmapProgress::where('user_id', $user->id)->get();
        $streak   = UserStreak::where('user_id', $user->id)->first();

        $earnedAchievements = UserAchievement::with('achievement')
                                ->where('user_id', $user->id)
                                ->orderByDesc('earned_at')
                                ->get();

        $totalTasks     = UserTaskProgress::where('user_id', $user->id)->count();
        $completedTasks = UserTaskProgress::where('user_id', $user->id)
                                         ->where('status', 'completed')
                                         ->count();

        return [
            // Roadmap stats
            'roadmaps_started'    => $roadmaps->where('status', 'in_progress')->count(),
            'roadmaps_completed'  => $roadmaps->where('status', 'completed')->count(),
            'overall_progress'    => $roadmaps->count() > 0
                                        ? round($roadmaps->avg('percent_complete'), 1)
                                        : 0,

            // Task stats
            'total_tasks'         => $totalTasks,
            'completed_tasks'     => $completedTasks,

            // Streak stats
            'current_streak'      => $streak?->current_streak ?? 0,
            'longest_streak'      => $streak?->longest_streak ?? 0,

            // Achievement stats
            'achievements_earned' => $earnedAchievements->count(),
            'total_points'        => $earnedAchievements->sum(fn($ua) => $ua->achievement->points ?? 0),
            'recent_achievements' => $earnedAchievements->take(5)->map(fn($ua) => [
                'title'     => $ua->achievement->title,
                'icon'      => $ua->achievement->icon,
                'color'     => $ua->achievement->color,
                'points'    => $ua->achievement->points,
                'earned_at' => $ua->earned_at,
            ]),
        ];
    }
}