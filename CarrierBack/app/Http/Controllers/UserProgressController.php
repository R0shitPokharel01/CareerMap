<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserRoadmapProgress;
use App\Models\UserTaskProgress;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * User Progress Controller
 *
 * Handles:
 *  - Starting a task
 *  - Completing a task
 *  - Getting roadmap progress
 *  - Getting the full profile summary (progress tracking page)
 */
class ProgressController extends Controller
{
    public function __construct(
        private ProgressService $progressService
    ) {}

    // POST /api/user/tasks/{taskId}/start
    // Mark a task as in_progress
    public function startTask(Request $request, int $taskId): JsonResponse
    {
        $request->validate([
            'roadmap_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $task = $this->progressService->startTask($user, $taskId, $request->roadmap_id);

        return response()->json([
            'message' => 'Task started.',
            'task'    => $task,
        ]);
    }

    // POST /api/user/tasks/{taskId}/complete
    // Mark a task as completed — also updates roadmap % and checks achievements
    public function completeTask(Request $request, int $taskId): JsonResponse
    {
        $request->validate([
            'roadmap_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $task = $this->progressService->completeTask($user, $taskId, $request->roadmap_id);

        // Get the updated roadmap progress after completion
        $roadmapProgress = UserRoadmapProgress::where('user_id', $user->id)
                                ->where('roadmap_id', $request->roadmap_id)
                                ->first();

        return response()->json([
            'message'          => 'Task completed!',
            'task'             => $task,
            'roadmap_progress' => $roadmapProgress,
        ]);
    }

    // GET /api/user/roadmaps/{roadmapId}/progress
    // Get progress details for a specific roadmap
    public function roadmapProgress(int $roadmapId): JsonResponse
    {
        $user = Auth::user();

        $progress = UserRoadmapProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $roadmapId)
                        ->first();

        $tasks = UserTaskProgress::where('user_id', $user->id)
                    ->where('roadmap_id', $roadmapId)
                    ->get();

        return response()->json([
            'roadmap_id'       => $roadmapId,
            'percent_complete' => $progress?->percent_complete ?? 0,
            'status'           => $progress?->status ?? 'not_started',
            'started_at'       => $progress?->started_at,
            'completed_at'     => $progress?->completed_at,
            'tasks'            => $tasks,
        ]);
    }

    // GET /api/user/progress/summary
    // Full profile summary — used on the Profile / Progress Tracking page
    public function summary(): JsonResponse
    {
        $user    = Auth::user();
        $summary = $this->progressService->getProfileSummary($user);

        return response()->json($summary);
    }

    // GET /api/user/progress/roadmaps
    // All roadmaps the user has started or completed
    public function allRoadmaps(): JsonResponse
    {
        $user = Auth::user();

        $roadmaps = UserRoadmapProgress::where('user_id', $user->id)
                        ->orderByDesc('updated_at')
                        ->get();

        return response()->json([
            'total'    => $roadmaps->count(),
            'roadmaps' => $roadmaps,
        ]);
    }
}