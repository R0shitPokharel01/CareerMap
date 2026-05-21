<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
            'password'=>bcrypt($request->password)
        ]);

        Auth::login($user);

        return response()->json([
            'message'=>'Registration Success'
        ],200);}
    }


    public function login(Request $request){
      $credentials =  $request->validate([
            'email'=>'required | email',
            'password'=>'required'
        ]);

        if(Auth::attempt($credentials)){
            $user = Auth::user();

            return response()->json([
                'success'=>true,
                'data'=>$user,
            ],200);

          //  return redirect('/welcome');
        }



        return response()->json([
                'success'=>false,
                'error'=>'Invalid Email or Password',
            ],401);

    }
}