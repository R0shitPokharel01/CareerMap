<?php

namespace App\Services\AdminServices;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{

    //  list User

    public function allUsers()
    {
        return User::all();
    }

    public function getUserById(int $id)
    {
        return User::findOrFail($id);
    }

    // Add User
    public function addUser(array $credentials)
    {
        if (User::where('email', $credentials['email'])->exists()) {
            return [
                'success' => false,
                'message' => 'User already exists!'
            ];
        } else {
            $user = User::create([
                'name' => $credentials['name'],
                'email' => $credentials['email'],
                'role' => $credentials['role'],
                'password' => bcrypt($credentials['password'])
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'success' => true,
                'message' => 'Registration Success',
                'token' => $token
            ];
        }
    }

    // Edit User
    public function editUser(int $userID, array $data)
    {
        $user = User::findOrFail($userID);

        $user->update([

            'role' => $data['role'],
        ]);

        //dd($user);

        return $user;
    }

    // Delete User
    public function deleteUser(int $userID, Request $request)
    {
        if ($userID === $request->user()->id) {
            throw new \Exception('You cannot delete your own account.');
        }
        $user = User::findOrFail($userID);

        $user->delete();

        return true;
    }
}
