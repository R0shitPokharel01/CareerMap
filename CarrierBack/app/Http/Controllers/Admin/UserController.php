<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminServices\UserManagementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class UserController extends Controller
{
    // All Users
    public function allUsers(UserManagementService $userManagementService)
    {
        try {
            $users = $userManagementService->allUsers();

            return response()->json($users);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch users.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get Single User
    public function getUserById(int $id, UserManagementService $userManagementService)
    {
        try {
            $user = $userManagementService->getUserById($id);

            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Add User
    public function addUser(Request $request, UserManagementService $userManagementService)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'role' => 'required'
            ]);

            $user = $userManagementService->addUser($validated);

            return response()->json([
                'message' => 'User added successfully.',
                'data' => $user
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to add user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Edit User
    public function editUser(Request $request, int $userID, UserManagementService $userManagementService)
    {
        try {
            $validated = $request->validate([
                'role' => 'required'
            ]);

            $user = $userManagementService->editUser($userID, $validated);

            return response()->json([
                'message' => 'User updated successfully.',
                'data' => $user
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete User
    public function deleteUser(int $userID, UserManagementService $userManagementService)
    {
        try {
            $userManagementService->deleteUser($userID);

            return response()->json([
                'message' => 'User deleted successfully.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}