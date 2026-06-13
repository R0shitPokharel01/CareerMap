<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\AuthServices\LoginService;
use Exception;
//use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    //Register Function
    public function register(Request $request){
       $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|min:8'
        ]);

        if (User::where('email',$request->email)->exists()) {
            return response()->json([
            'message'=>'User already exists!'
        ],409);
        }else{
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'role'=>'user',
            'password'=>bcrypt($request->password)
        ]);

        Auth::login($user);

        return response()->json([
            'message'=>'Registration Success'
        ],200);}
    }

    // Login Function
    public function login(Request $request){
    try {
        $credentials =  $request->validate([
            'email'=>'required|email',
            'password'=>'required|min:8'
        ]);

        $loginUser = new LoginService();
        $result = $loginUser->login($credentials);

        if($result['success']){
            return response()->json([
                'message'=>'Login Successful',
                'user'=>$result['user'],
                'token'=>$result['token']
            ]);
        }

    }catch (Exception $e) {
        return response()->json([
        'message'=>$e->getMessage()
        ]);
    }

    }



    // Logout Function
    public function logout(Request $request){
        Auth::logout();

        return response()->json([
            'message'=>'Logout Successful'
        ],200);
    }

}
