<?php

namespace App\Http\Controllers;

use App\Http\Controllers\User\UserProgressController;
use App\Http\Controllers\User\UserAcheivementsController as UserAchievementsController;
use App\Services\DashboardServices\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        DashboardService $dashboardService,
        UserProgressController $progressController,
        UserAchievementsController $userAchievementsController
    ) {
        return response()->json(
            $dashboardService->index($request, $progressController, $userAchievementsController)
        );
    }
}
