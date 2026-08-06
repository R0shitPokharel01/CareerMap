<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achivements;
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
        $achievements = Achivements::withCount('users')
            ->latest()
            ->paginate(15);

        return response()->json($achievements);
    }

    // POST /api/admin/achievements

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'colour' => ['required', 'string', 'max:20'],
            'points' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $achievement = Achivements::create($validated);

        return response()->json([
            'message' => 'Achievement created successfully.',
            'achievement' => $achievement,
        ], 201);
    }

    // GET /api/admin/achievements/{id}
    public function show(Achivements $achievement): JsonResponse
    {
        $achievement->loadCount('users');
        $achievement->load([
            'users' => fn($q) => $q->latest('earned_at')->take(10)
        ]);

        return response()->json($achievement);
    }

    // PUT /api/admin/achievements/{id}
    public function update(Request $request, Achivements $achievement)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'points' => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $achievement->update($validated);

        return response()->json([
            'message' => 'Achievement updated successfully.',
            'achievement' => $achievement,
        ]);
    }

    // PATCH /api/admin/achievements/{id}/toggle
    public function toggle(Achivements $achievement): JsonResponse
    {
        $achievement->update(['is_active' => !$achievement->is_active]);

        return response()->json([
            'message'   => 'Achievement ' . ($achievement->is_active ? 'activated' : 'deactivated') . '.',
            'is_active' => $achievement->is_active,
        ]);
    }

    // DELETE /api/admin/achievements/{id}
    public function destroy(Achivements $achievement): JsonResponse
    {
        $achievement->delete();

        return response()->json([
            'message' => 'Achievement deleted successfully.',
        ]);
    }

    // GET /api/admin/achievements/stats
    public function stats(): JsonResponse
    {
        $achievements = Achivements::withCount('users')->get()
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
