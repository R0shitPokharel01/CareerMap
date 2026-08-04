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
class UserProgressController extends Controller
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

        $careers = \App\Models\Careers::with('phases')
            ->where('is_published', true)
            ->get();

        $roadmapProgress = UserRoadmapProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('roadmap_id');

        $taskProgress = UserTaskProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('task_id');

        $result = $careers->map(function ($career) use ($roadmapProgress, $taskProgress) {
            $steps = $career->phases->sortBy('sequence_num')->values()->map(function ($phase) use ($taskProgress) {
                $progress = $taskProgress->get($phase->id);
                $status = $progress->status ?? 'not_started';

                return [
                    'id'          => $phase->id,
                    'title'       => $phase->title,
                    'description' => $phase->description,
                    'skills'      => $phase->skills ?? [],
                    'progress'    => match ($status) {
                        'completed'   => 100,
                        'in_progress' => 50,
                        default       => 0,
                    },
                    'completed'   => $status === 'completed',
                ];
            });

            return [
                'id'          => $career->id,
                'title'       => $career->title,
                'description' => $career->description,
                'category'    => $career->category,
                'steps'       => $steps,
            ];
        });

        return response()->json($result);
    }
}