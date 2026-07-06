<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminServices\UserManagementService;

class UserController extends Controller
{
    // All Users
    public function allUsers(Request $request, UserManagementService $userManagementService)
    {

        $users = $userManagementService->allUsers();

        return response()->json($users);
    }


    // Add User
    public function addUser(Request $request, UserManagementService $userManagementService)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        $user = $userManagementService->addUser($request->all());

        return response()->json([
            'message' => 'User added successfully.',
            'data' => $user
        ], 201);
    }

    // Edit User
    public function editUser(Request $request, int $userID, UserManagementService $userManagementService)
    {
        $user = $userManagementService->editUser($userID, $request->all());

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user
        ]);
    }

    // Delete User
    public function deleteUser(int $userID,  UserManagementService $userManagementService)
    {
        $userManagementService->deleteUser($userID);

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}
