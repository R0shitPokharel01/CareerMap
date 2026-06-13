<?php
namespace App\Services\AuthServices;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginService {
    public function login(array $credentials){

        if(Auth::attempt($credentials)){
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'success'=>true,
                'user'=>$user,
                'token'=>$token
            ];

        }else{

            return [
                'success'=>false,
                'message'=>'Invalid Email or Password',
            ];

        }


    }
}


?>
