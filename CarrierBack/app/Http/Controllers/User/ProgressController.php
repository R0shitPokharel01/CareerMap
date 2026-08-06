<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Careers;
use App\Models\Phases;
use App\Models\UserRoadmapProgress;
use App\Models\Usertaskprogress;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Achivements;
use App\Models\UserAchievements;
use App\Services\AchievementService;

class ProgressController extends Controller
{
    public function __construct(
        private ProgressService $progressService,
        private AchievementService $achievementService
    ) {}

    public function startCareer(
        Request $request,
        int $careerId
    ): JsonResponse {
        $user = $request->user();

        $career = Careers::with('phases.resources')
            ->whereKey($careerId)
            ->where('is_published', true)
            ->first();

        if (!$career) {
            return response()->json([
                'message' => 'Career not found or not published.',
            ], 404);
        }

        $existingProgress = UserRoadmapProgress::query()
            ->where('user_id', $user->id)
            ->where('roadmap_id', $career->id)
            ->first();

        if ($existingProgress) {
            return response()->json([
                'message' => 'This roadmap has already been started.',
                'roadmap' => $this->formatCareer(
                    $career,
                    $existingProgress
                ),
            ]);
        }

        $roadmapProgress = DB::transaction(
            function () use ($user, $career) {
                $roadmapProgress =
                    UserRoadmapProgress::create([
                        'user_id' => $user->id,
                        'roadmap_id' => $career->id,
                        'progress_percentage' => 0,
                        'status' => 'in_progress',
                        'started_at' => now(),
                        'completed_at' => null,
                    ]);

                foreach ($career->phases as $phase) {
                    Usertaskprogress::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'roadmap_id' => $career->id,
                            'task_id' => $phase->id,
                        ],
                        [
                            'status' => 'pending',
                        ]
                    );
                }

                return $roadmapProgress;
            }
        );

        return response()->json([
            'message' => 'Career roadmap started successfully.',
            'roadmap' => $this->formatCareer(
                $career,
                $roadmapProgress
            ),
        ], 201);
    }

    public function startTask(
        Request $request,
        int $taskId
    ): JsonResponse {
        $user = $request->user();
        $phase = Phases::find($taskId);

        if (!$phase) {
            return response()->json([
                'message' => 'Phase not found.',
            ], 404);
        }

        $roadmap = UserRoadmapProgress::query()
            ->where('user_id', $user->id)
            ->where('roadmap_id', $phase->career_id)
            ->first();

        if (!$roadmap) {
            return response()->json([
                'message' => 'Start the career roadmap first.',
            ], 422);
        }

        $progress = Usertaskprogress::updateOrCreate(
            [
                'user_id' => $user->id,
                'roadmap_id' => $phase->career_id,
                'task_id' => $phase->id,
            ],
            [
                'status' => 'in_progress',
            ]
        );

        return response()->json([
            'message' => 'Phase started successfully.',
            'task_progress' => $progress,
        ]);
    }

    public function completeTask(
        Request $request,
        int $taskId
    ): JsonResponse {
        $user = $request->user();
        $phase = Phases::find($taskId);

        if (!$phase) {
            return response()->json([
                'message' => 'Phase not found.',
            ], 404);
        }

        $progress = Usertaskprogress::query()
            ->where('user_id', $user->id)
            ->where('roadmap_id', $phase->career_id)
            ->where('task_id', $phase->id)
            ->first();

        if (!$progress) {
            return response()->json([
                'message' => 'Start this phase first.',
            ], 422);
        }

        $progress->update([
            'status' => 'completed',
        ]);

        $this->updateRoadmapProgress(
            $user->id,
            $phase->career_id
        );

        $newAchievements = $this->achievementService->checkAndAward($user);

        return response()->json([
            'message' => 'Phase completed successfully.',
            'task_progress' => $progress->fresh(),

            'new_achievements' => collect($newAchievements)
                ->map(fn($achievement) => [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' =>
                    $achievement->description,
                    'icon' => $achievement->icon,
                    'points' => (int) $achievement->points,
                ])
                ->values(),
        ]);
    }

    public function roadmapProgress(
        Request $request,
        int $careerId
    ): JsonResponse {
        $user = $request->user();

        $progress = UserRoadmapProgress::query()
            ->where('user_id', $user->id)
            ->where('roadmap_id', $careerId)
            ->first();

        $tasks = Usertaskprogress::query()
            ->where('user_id', $user->id)
            ->where('roadmap_id', $careerId)
            ->get();

        return response()->json([
            'career_id' => $careerId,
            'progress_percentage' =>
            $progress?->status === 'completed' ? 100 : 0,
            'status' => $progress?->status ?? 'not_started',
            'phases_progress' => $tasks,
        ]);
    }

    public function summary(
        Request $request
    ): JsonResponse {
        $summary = $this->progressService->getProfileSummary(
            $request->user()
        );

        return response()->json($summary);
    }

    public function allRoadmaps(
        Request $request
    ): JsonResponse {
        $user = $request->user();

        $roadmapProgressList = UserRoadmapProgress::with([
            'career.phases.resources',
        ])
            ->where('user_id', $user->id)
            ->latest('started_at')
            ->get();

        $roadmaps = $roadmapProgressList
            ->map(function (
                UserRoadmapProgress $roadmapProgress
            ) use ($user) {
                $career = $roadmapProgress->career;

                if (!$career) {
                    return null;
                }

                $phaseProgress = Usertaskprogress::query()
                    ->where('user_id', $user->id)
                    ->where('roadmap_id', $career->id)
                    ->get()
                    ->keyBy('task_id');

                $phases = $career->phases->map(
                    function ($phase) use ($phaseProgress) {
                        $progress = $phaseProgress->get(
                            $phase->id
                        );

                        return [
                            'id' => $phase->id,
                            'career_id' => $phase->career_id,
                            'sequence_num' =>
                            $phase->sequence_num,
                            'title' => $phase->title,
                            'description' =>
                            $phase->description,
                            'level' => $phase->level,
                            'duration_range' =>
                            $phase->duration_range,
                            'skills' => $phase->skills,
                            'milestone' => $phase->milestone,
                            'status' =>
                            $progress?->status ??
                                'not_started',
                            'progress_percentage' =>
                            (int) (
                                $progress
                                ?->progress_percentage ??
                                0
                            ),
                            'started_at' =>
                            $progress?->started_at,
                            'completed_at' =>
                            $progress?->completed_at,
                            'resources' => $phase->resources,
                        ];
                    }
                );

                return [
                    'id' => $roadmapProgress->id,
                    'career_id' => $career->id,
                    'roadmap_id' =>
                    $roadmapProgress->roadmap_id,
                    'title' => $career->title,
                    'description' => $career->description,
                    'category' => $career->category,
                    'duration' => $career->duration,
                    'progress_percentage' =>
                    (int) (
                        $roadmapProgress
                        ->progress_percentage ??
                        0
                    ),
                    'status' => $roadmapProgress->status,
                    'started_at' =>
                    $roadmapProgress->started_at,
                    'completed_at' =>
                    $roadmapProgress->completed_at,
                    'phases' => $phases,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'total' => $roadmaps->count(),
            'roadmaps' => $roadmaps,
        ]);
    }

    private function updateRoadmapProgress(
        int $userId,
        int $careerId
    ): void {
        $totalPhases = Phases::query()
            ->where('career_id', $careerId)
            ->count();

        $completedPhases = Usertaskprogress::query()
            ->where('user_id', $userId)
            ->where('roadmap_id', $careerId)
            ->where('status', 'completed')
            ->count();

        $percentage = $totalPhases > 0
            ? (int) round(
                ($completedPhases / $totalPhases) * 100
            )
            : 0;

        UserRoadmapProgress::query()
            ->where('user_id', $userId)
            ->where('roadmap_id', $careerId)
            ->update([
                'progress_percentage' => $percentage,
                'status' => $percentage >= 100
                    ? 'completed'
                    : 'in_progress',
                'completed_at' => $percentage >= 100
                    ? now()
                    : null,
            ]);
    }

    private function formatCareer(
        Careers $career,
        UserRoadmapProgress $progress
    ): array {
        return [
            'id' => $progress->id,
            'career_id' => $career->id,
            'roadmap_id' => $progress->roadmap_id,
            'title' => $career->title,
            'description' => $career->description,
            'category' => $career->category,
            'duration' => $career->duration,
            'progress_percentage' =>
            (int) ($progress->progress_percentage ?? 0),
            'status' => $progress->status,
            'started_at' => $progress->started_at,
            'completed_at' => $progress->completed_at,
            'phases' => $career->phases,
        ];
    }
}
