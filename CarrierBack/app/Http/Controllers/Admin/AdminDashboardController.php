<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achivements;
use App\Models\User;
use App\Models\Careeers;
use App\Models\Careers;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();

        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'avatar' => $admin->avatar ?? null,
            ],

            'summary' => [
                'total_users' => User::count(),
                'active_users' => User::count(),
                'admin_users' => User::where('role', 'admin')->count(),
                'total_achievements' => Achivements::count(),
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
                    'category'
                ])

        ]);
    }
}
