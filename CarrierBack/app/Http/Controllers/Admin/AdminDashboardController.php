<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Achievement;
use App\Models\Careers;
use App\Models\UserAchievements;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{


    public function dashboard()
    {
        return response()->json([

            'message' => 'Dashboard data fetched successfully',

            'admin' => Auth::user(),

            'summary' => [

                'users' => User::count(),

                'careers' => Careers::count(),

                'roadmaps' => Careers::count(),

                'achievements' => UserAchievements::count(),

            ],

            'recent_users' => User::latest()
                ->take(5)
                ->get([
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at'
                ]),

            'recent_careers' => Careers::latest()
                ->take(5)
                ->get([
                    'id',
                    'title',
                    'category',
                    'difficulty'
                ])

        ]);
    }
}
