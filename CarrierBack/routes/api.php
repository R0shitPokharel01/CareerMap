<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\Career\CareerAiController;

// for login
Route::post('/login', [AuthController::class, 'login']);

//  for register
Route::post('/register', [AuthController::class, 'register']);

//  for logout
Route::post('/logout', [AuthController::class, 'logout']);

//  for new career generation
Route::post('/new-career', [CareerAiController::class, 'newCareer']);

//  for career search
Route::get('/careers/q', [CareerController::class, 'search']);

// for Career By user
Route::get('/careers/my-careers', [CareerController::class, 'careerByUser']);

//   for all Careers
Route::get('/careers', [CareerController::class, 'all']);

Route::get('/', function () {
    return "API Working";
});
