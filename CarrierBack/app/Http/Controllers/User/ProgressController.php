<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserRoadmapProgress;
use App\Models\UserTaskProgress;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function __construct(
        private ProgressService $progressService
    ) {}

    public function startTask(Request $request, int $taskId): JsonResponse
    {
        $request->validate(['roadmap_id' => 'required|integer']);
        $user = Auth::user();
        $task = $this->progressService->startTask($user, $taskId, $request->roadmap_id);
        return response()->json(['message' => 'Task started.', 'task' => $task]);
    }

    public function completeTask(Request $request, int $taskId): JsonResponse
    {
        $request->validate(['roadmap_id' => 'required|integer']);
        $user = Auth::user();
        $task = $this->progressService->completeTask($user, $taskId, $request->roadmap_id);
        $roadmapProgress = UserRoadmapProgress::where('user_id', $user->id)
                            ->where('roadmap_id', $request->roadmap_id)->first();
        return response()->json([
            'message'          => 'Task completed!',
            'task'             => $task,
            'roadmap_progress' => $roadmapProgress,
        ]);
    }

    public function roadmapProgress(int $roadmapId): JsonResponse
    {
        $user     = Auth::user();
        $progress = UserRoadmapProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $roadmapId)->first();
        $tasks    = UserTaskProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $roadmapId)->get();
        return response()->json([
            'roadmap_id'       => $roadmapId,
            'percent_complete' => $progress?->percent_complete ?? 0,
            'status'           => $progress?->status ?? 'not_started',
            'started_at'       => $progress?->started_at,
            'completed_at'     => $progress?->completed_at,
            'tasks'            => $tasks,
        ]);
    }

    public function summary(): JsonResponse
    {
        $user    = Auth::user();
        $summary = $this->progressService->getProfileSummary($user);
        return response()->json($summary);
    }

    public function allRoadmaps(): JsonResponse
    {
        $user     = Auth::user();
        $roadmaps = UserRoadmapProgress::where('user_id', $user->id)
                        ->orderByDesc('updated_at')->get();
        return response()->json(['total' => $roadmaps->count(), 'roadmaps' => $roadmaps]);
    }
}