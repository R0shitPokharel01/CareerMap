<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Achivements;
use App\Models\UserAchievements;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * User Achievements Controller
 * user can:
 * -see all achievements with earned/locked status
 * -see only their earned achievements
 * -trigger a check after completing a task
 */
class UserAchievementsController extends Controller
{
    public function __construct(
        private AchievementService $achievementService
    ) {}

    // GET /api/user/achievements
    // All achievements - earned ones show unlocked, rest show locked
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $earnedMap = UserAchievements::where('user_id', $user->id)
            ->pluck('earned_at', 'achievement_id');

        $achievements = Achivements::active()->get()->map(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->description,
            'icon' => $a->icon,
            'color' => $a->color,
            'points' => $a->points,
            'is_earned' => $earnedMap->has($a->id),
            'earned_at' => $earnedMap->get($a->id),
        ]);

        return response()->json([
            'total' => $achievements->count(),
            'earned' => $achievements->where('is_earned', true)->count(),
            'locked' => $achievements->where('is_earned', false)->count(),
            'data' => $achievements->values(),
        ]);
    }

    // GET /api/user/achievements/earned
    // Only earned achievements - used on the profile page
    public function earned(): JsonResponse
    {
        $user = Auth::user();

        $earnedRecords = UserAchievements::with('achievement')
            ->where('user_id', $user->id)
            ->orderByDesc('earned_at')
            ->get();

        $earned = $earnedRecords->map(fn($ua) => [
            'id' => $ua->achievement->id,
            'title' => $ua->achievement->title,
            'description' => $ua->achievement->description,
            'icon' => $ua->achievement->icon,
            'color' => $ua->achievement->color,
            'points' => $ua->achievement->points,
            'earned_at' => $ua->earned_at,
        ]);

        return response()->json([
            'total' => $earned->count(),
            'total_points' => $earned->sum('points'),
            'achievements' => $earned,
        ]);
    }

    // POST /api/user/achievements/check
    // Trigger achievement check - call this after user completes a task
    public function check(): JsonResponse
    {
        $user = Auth::user();
        $newlyEarned = $this->achievementService->checkAndAward($user);

        return response()->json([
            'message' => count($newlyEarned) > 0 ? count($newlyEarned) . ' new achievement(s) earned!' : 'No new achievements at this time.',
            'newly_earned' => collect($newlyEarned)->map(fn($a) => [
                'id' => $a->id,
                'title'       => $a->title,
                'description' => $a->description,
                'icon'        => $a->icon,
                'color'       => $a->color,
                'points'      => $a->points,
            ]),
        ]);
    }
}
