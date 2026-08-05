<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRoadmapProgress;
use App\Models\UserTaskProgress;
use App\Models\UserStreak;
use App\Models\UserAchievements;
use App\Models\Phases;   
use Carbon\Carbon;
class ProgressService
{
    public function __construct(
        private AchievementService $achievementService
    ) {}


    public function completeTask(User $user, int $phaseId, int $careerId): UserTaskProgress
    {
        $task = UserTaskProgress::updateOrCreate(
            ['user_id' => $user->id, 'task_id' => $phaseId],
            [
                'roadmap_id'   => $careerId,
                'status'       => 'completed',
                'completed_at' => now(),
            ]
        );

        $this->recalculateRoadmapProgress($user, $careerId);
        $this->updateStreak($user);
        $this->achievementService->checkAndAward($user);

        return $task;
    }

    public function startTask(User $user, int $phaseId, int $careerId): UserTaskProgress
    {
        $task = UserTaskProgress::updateOrCreate(
            ['user_id' => $user->id, 'task_id' => $phaseId],
            [
                'roadmap_id' => $careerId,
                'status'     => 'in_progress',
            ]
        );

        UserRoadmapProgress::updateOrCreate(
            ['user_id' => $user->id, 'roadmap_id' => $careerId],
            [
                'status'     => 'in_progress',
                'started_at' => now(),
            ]
        );

        return $task;
    }


    public function recalculateRoadmapProgress(User $user, int $careerId): UserRoadmapProgress
    {
        $total = Phases::where('career_id', $careerId)->count();

        $completed = UserTaskProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $careerId)
                        ->where('status', 'completed')
                        ->count();

        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $status = match (true) {
            $percent === 0   => 'not_started',
            $percent === 100 => 'completed',
            default          => 'in_progress',
        };

        return UserRoadmapProgress::updateOrCreate(
            ['user_id' => $user->id, 'roadmap_id' => $careerId],
            [
                'percent_complete' => $percent,
                'status'           => $status,
                'completed_at'     => $status === 'completed' ? now() : null,
            ]
        );
    }


    public function updateStreak(User $user): UserStreak
    {
        $streak = UserStreak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0]
        );

        $today      = Carbon::today()->toDateString();
        $yesterday  = Carbon::yesterday()->toDateString();
        $lastActive = $streak->last_active_date?->toDateString();

        if ($lastActive === $today) return $streak; 

        $streak->current_streak   = ($lastActive === $yesterday)
                                        ? $streak->current_streak + 1
                                        : 1;
        $streak->last_active_date = $today;
        $streak->save();

        return $streak;
    }


    public function getProfileSummary(User $user): array
    {
        $roadmaps = UserRoadmapProgress::where('user_id', $user->id)->get();
        $streak   = UserStreak::where('user_id', $user->id)->first();

        $earnedAchievements = UserAchievements::with('achivement')
                                ->where('user_id', $user->id)
                                ->orderByDesc('earned_at')
                                ->get();

        $totalTasks     = UserTaskProgress::where('user_id', $user->id)->count();
        $completedTasks = UserTaskProgress::where('user_id', $user->id)
                            ->where('status', 'completed')->count();

        return [
            'roadmaps_started'    => $roadmaps->where('status', 'in_progress')->count(),
            'roadmaps_completed'  => $roadmaps->where('status', 'completed')->count(),
            'overall_progress'    => $roadmaps->count() > 0
                                        ? round($roadmaps->avg('percent_complete'), 1)
                                        : 0,
            'total_tasks'         => $totalTasks,
            'completed_tasks'     => $completedTasks,
            'current_streak'      => $streak?->current_streak ?? 0,
            'achievements_earned' => $earnedAchievements->count(),
            'total_points'        => $earnedAchievements->sum(
                                        fn($ua) => $ua->achivement->points ?? 0
                                    ),
            'recent_achievements' => $earnedAchievements->take(5)->map(fn($ua) => [
                'title'     => $ua->achivement->title,
                'icon'      => $ua->achivement->icon,
                'earned_at' => $ua->earned_at,
            ]),
        ];
    }
}