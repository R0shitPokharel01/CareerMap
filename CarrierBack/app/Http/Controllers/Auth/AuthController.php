<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthServices\RegisterService;
use App\Services\AuthServices\LoginService;
use Exception;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{

    //Register Function

    public function register(Request $request, RegisterService $registerService)
    {
        try {
            $credentials = $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            $result = $registerService->register($credentials);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'token' => $result['token']
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 409);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            return response()->json(['message' => 'Something Went Wrong.'], 500);
        }
    }

    // Login Function
    public function login(Request $request, LoginService $login)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);

            $result = $login->login($credentials);

            if ($result['success']) {
                return response()->json([
                    'message' => 'Login Successful',
                    'user' => $result['user'],
                    'token' => $result['token']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed, try again.'
            ], 500);
        }
    }
    // Logout Function
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout Successful'
        ], 200);
    }
}
