<?php

namespace App\Services\AuthServices;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\WelcomeNotification;

class RegisterService
{
    public function register(array $credentials)
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
                'role' => 'user',
                'password' => bcrypt($credentials['password'])
            ]);

            $user->notify(new WelcomeNotification());

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'success' => true,
                'message' => 'Registration Success',
                'token' => $token
            ];
        }
    }
}
