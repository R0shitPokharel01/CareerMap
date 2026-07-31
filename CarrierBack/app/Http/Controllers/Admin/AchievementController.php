<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Admin Achievement Controller
 *
 * Admin can:
 *  - View all achievements with stats
 *  - Create achievements with custom conditions
 *  - Edit achievements
 *  - Toggle active/inactive
 *  - Delete achievements
 *  - View overall stats
 */
class AchievementController extends Controller
{
    // GET /api/admin/achievements
    public function index(): JsonResponse
    {
        $achievements = Achievement::withCount('users')
                                   ->latest()
                                   ->paginate(15);

        return response()->json($achievements);
    }

    // POST /api/admin/achievements
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'icon'        => 'required|string|max:50',
            'color'       => 'required|string|max:7',
            'type'        => 'required|in:task_completion,roadmap_completion,roadmap_progress,streak,profile_complete',
            'condition'   => 'required|array',
            'points'      => 'sometimes|integer|min:1|max:1000',
            'is_active'   => 'sometimes|boolean',
        ]);

        $achievement = Achievement::create($data);

        return response()->json([
            'message'     => 'Achievement created successfully.',
            'achievement' => $achievement,
        ], 201);
    }

    // GET /api/admin/achievements/{id}
    public function show(Achievement $achievement): JsonResponse
    {
        $achievement->loadCount('users');
        $achievement->load([
            'users' => fn($q) => $q->latest('earned_at')->take(10)
        ]);

        return response()->json($achievement);
    }

    // PUT /api/admin/achievements/{id}
    public function update(Request $request, Achievement $achievement): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'sometimes|string|max:100',
            'description' => 'sometimes|string|max:500',
            'icon'        => 'sometimes|string|max:50',
            'color'       => 'sometimes|string|max:7',
            'type'        => 'sometimes|in:task_completion,roadmap_completion,roadmap_progress,streak,profile_complete',
            'condition'   => 'sometimes|array',
            'points'      => 'sometimes|integer|min:1|max:1000',
            'is_active'   => 'sometimes|boolean',
        ]);

        $achievement->update($data);

        return response()->json([
            'message'     => 'Achievement updated successfully.',
            'achievement' => $achievement,
        ]);
    }

    // PATCH /api/admin/achievements/{id}/toggle
    public function toggle(Achievement $achievement): JsonResponse
    {
        $achievement->update(['is_active' => !$achievement->is_active]);

        return response()->json([
            'message'   => 'Achievement ' . ($achievement->is_active ? 'activated' : 'deactivated') . '.',
            'is_active' => $achievement->is_active,
        ]);
    }

    // DELETE /api/admin/achievements/{id}
    public function destroy(Achievement $achievement): JsonResponse
    {
        $achievement->delete();

        return response()->json([
            'message' => 'Achievement deleted successfully.',
        ]);
    }

    // GET /api/admin/achievements/stats
    public function stats(): JsonResponse
    {
        $achievements = Achievement::withCount('users')->get()
            ->map(fn($a) => [
                'id'        => $a->id,
                'title'     => $a->title,
                'type'      => $a->type,
                'earned_by' => $a->users_count,
                'points'    => $a->points,
                'is_active' => $a->is_active,
            ])
            ->sortByDesc('earned_by')
            ->values();

        return response()->json([
            'total_achievements' => $achievements->count(),
            'total_awarded'      => $achievements->sum('earned_by'),
            'most_earned'        => $achievements->first(),
            'rarest'             => $achievements->last(),
            'all'                => $achievements,
        ]);
    }
}