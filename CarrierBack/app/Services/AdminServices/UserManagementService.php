<?php

namespace App\Services\AdminServices;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserManagementService
{

    //  list User

    public function allUsers()
    {
        return User::all();
    }

    // Add User
    public function addUser(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role']
        ]);
    }

    // Edit User
    public function editUser(int $userID, array $data)
    {
        $user = User::findOrFail($userID);

        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'role' => $data['role'] ?? $user->role,
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        return $user;
    }

    // Delete User
    public function deleteUser(int $userID)
    {
        $user = User::findOrFail($userID);

        $user->delete();

        return true;
    }
}
