<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Achivements;
use App\Models\UserAchievements;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAchievementsController extends Controller
{
    public function __construct(
        private AchievementService $achievementService
    ) {}

    // GET /api/user/achievements
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $earnedMap = UserAchievements::query()
            ->where('user_id', $user->id)
            ->pluck('earned_at', 'achievement_id');

        $achievements = Achivements::query()
            ->where('is_active', true)
            ->get()
            ->map(function ($achievement) use ($earnedMap) {
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' => $achievement->description,
                    'icon' => $achievement->icon,
                    'points' => (int) $achievement->points,
                    'is_active' => (bool) $achievement->is_active,
                    'is_earned' => $earnedMap->has(
                        $achievement->id
                    ),
                    'earned_at' => $earnedMap->get(
                        $achievement->id
                    ),
                ];
            });

        return response()->json([
            'total' => $achievements->count(),

            'earned' => $achievements
                ->where('is_earned', true)
                ->count(),

            'locked' => $achievements
                ->where('is_earned', false)
                ->count(),

            'data' => $achievements->values(),
        ]);
    }

    // GET /api/user/achievements/earned
    public function earned(Request $request): JsonResponse
    {
        $user = $request->user();

        $earnedRecords = UserAchievements::with(
            'achievement'
        )
            ->where('user_id', $user->id)
            ->orderByDesc('earned_at')
            ->get();

        $earned = $earnedRecords
            ->filter(function ($record) {
                return $record->achievement !== null;
            })
            ->map(function ($record) {
                return [
                    'id' => $record->achievement->id,
                    'name' => $record->achievement->name,
                    'description' =>
                    $record->achievement->description,
                    'icon' => $record->achievement->icon,
                    'points' =>
                    (int) $record->achievement->points,
                    'earned_at' => $record->earned_at,
                ];
            })
            ->values();

        return response()->json([
            'total' => $earned->count(),
            'total_points' => $earned->sum('points'),
            'achievements' => $earned,
        ]);
    }

    // POST /api/user/achievements/check
    public function check(Request $request): JsonResponse
    {
        $user = $request->user();

        $newlyEarned =
            $this->achievementService->checkAndAward($user);

        $achievements = collect($newlyEarned)
            ->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' =>
                    $achievement->description,
                    'icon' => $achievement->icon,
                    'points' => (int) $achievement->points,
                ];
            })
            ->values();

        return response()->json([
            'message' => $achievements->isNotEmpty()
                ? $achievements->count() .
                ' new achievement(s) earned!'
                : 'No new achievements at this time.',

            'newly_earned' => $achievements,
        ]);
    }
}
