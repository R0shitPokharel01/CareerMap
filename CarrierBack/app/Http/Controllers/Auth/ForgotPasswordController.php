<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Exception;

class ForgotPasswordController extends Controller
{

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email']
            ]);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {

                return response()->json([
                    'success' => true,
                    'message' => __($status)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __($status)
            ], 400);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function resetPassword(Request $request)
    {
        //dd('This is reset pwd ');
        try {
            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email'],
                'password' => ['required', 'confirmed', 'min:8']
            ]);


            $status = Password::reset(

                $request->only(
                    'email',
                    'password',
                    'password_confirmation',
                    'token'
                ),

                function ($user, $password) {

                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(
                        Str::random(60)
                    );

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status === Password::PASSWORD_RESET) {

                return response()->json([
                    'success' => true,
                    'message' => 'Password reset successfully.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __($status)
            ], 400);
        } catch (Exception $e) {
            throw $e;
        }
    }
}