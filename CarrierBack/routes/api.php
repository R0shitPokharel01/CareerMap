<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// for login
Route::post('/login',[AuthController::class,'login']);

//  for register
Route::post('/register',[AuthController::class,'register']);

//  for logout
Route::post('/logout',[AuthController::class,'logout']);


Route::get('/test', function () {
    return "API Working";
});
