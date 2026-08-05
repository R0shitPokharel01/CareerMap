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

    public function startTask(Request $request, int $phaseId): JsonResponse
    {
        $request->validate(['career_id' => 'required|integer']);
        $user = Auth::user();
        $task = $this->progressService->startTask($user, $phaseId, $request->career_id);
        return response()->json(['message' => 'Phase started.', 'task' => $task]);
    }

    public function completeTask(Request $request, int $phaseId): JsonResponse
    {
        $request->validate(['career_id' => 'required|integer']);
        $user     = Auth::user();
        $task     = $this->progressService->completeTask($user, $phaseId, $request->career_id);
        $progress = UserRoadmapProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $request->career_id)
                        ->first();
        return response()->json([
            'message'         => 'Phase completed!',
            'task'            => $task,
            'career_progress' => $progress,
        ]);
    }

  
    public function roadmapProgress(int $careerId): JsonResponse
    {
        $user     = Auth::user();
        $progress = UserRoadmapProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $careerId)->first();
        $tasks    = UserTaskProgress::where('user_id', $user->id)
                        ->where('roadmap_id', $careerId)->get();
        return response()->json([
            'career_id'        => $careerId,
            'percent_complete' => $progress?->percent_complete ?? 0,
            'status'           => $progress?->status ?? 'not_started',
            'started_at'       => $progress?->started_at,
            'completed_at'     => $progress?->completed_at,
            'phases_progress'  => $tasks,
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
        return response()->json([
            'total'    => $roadmaps->count(),
            'roadmaps' => $roadmaps,
        ]);
    }
}