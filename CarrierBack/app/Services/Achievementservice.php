<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserRoadmapProgress;
use App\Models\UserTaskProgress;
use App\Models\UserStreak;
use App\Models\User;

class AchievementService
{

    public function checkAndAward(User $user): array
    {
        $newlyEarned = [];
        $pending = Achievement::active()
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('achievement_id')
                      ->from('user_achievements')
                      ->where('user_id', $user->id);
            })
            ->get();

        foreach ($pending as $achievement) {
            if ($this->conditionMet($user, $achievement)) {
                $this->award($user, $achievement);
                $newlyEarned[] = $achievement;
            }
        }

        return $newlyEarned;
    }

    // for checking whether a single achievement's condition is met.
    public function conditionMet(User $user, Achievement $achievement): bool
    {
        $condition = $achievement->condition;

        return match ($achievement->type) {
            'task_completion'    => $this->checkTaskCompletion($user, $condition),
            'roadmap_completion' => $this->checkRoadmapCompletion($user, $condition),
            'roadmap_progress'   => $this->checkRoadmapProgress($user, $condition),
            'streak'             => $this->checkStreak($user, $condition),
            'profile_complete'   => $this->checkProfileComplete($user),
            default              => false,
        };
    }

    private function checkTaskCompletion(User $user, array $condition): bool
    {
        $completed = UserTaskProgress::where('user_id', $user->id)
                        ->where('status', 'completed')
                        ->count();

        return $completed >= ($condition['count'] ?? 1);
    }
    
    private function checkRoadmapCompletion(User $user, array $condition): bool
    {
        return UserRoadmapProgress::where('user_id', $user->id)
                    ->where('roadmap_id', $condition['roadmap_id'])
                    ->where('status', 'completed')
                    ->exists();
    }

    private function checkRoadmapProgress(User $user, array $condition): bool
    {
        return UserRoadmapProgress::where('user_id', $user->id)
                    ->where('roadmap_id', $condition['roadmap_id'])
                    ->where('percent_complete', '>=', $condition['percent'])
                    ->exists();
    }

    private function checkStreak(User $user, array $condition): bool
    {
        $streak = UserStreak::where('user_id', $user->id)->first();

        if (!$streak) return false;

        return $streak->current_streak >= ($condition['days'] ?? 1)
            || $streak->longest_streak >= ($condition['days'] ?? 1);
    }

    private function checkProfileComplete(User $user): bool
    {
        return !empty($user->name)
            && !empty($user->email)
            && !empty($user->bio)
            && !empty($user->career_goal);
    }

    private function award(User $user, Achievement $achievement): void
    {
        UserAchievement::create([
            'user_id'        => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at'      => now(),
        ]);
    }
}