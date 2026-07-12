<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\Career\CareerAiController;
use App\Http\Controllers\Admin\UserController;

// for login
Route::post('/login', [AuthController::class, 'login']);

//  for register
Route::post('/register', [AuthController::class, 'register']);

//  for logout
Route::post('/logout', [AuthController::class, 'logout']);

//  for new career generation
Route::post('/new-career', [CareerAiController::class, 'newCareer']);

//  for career search
Route::get('/careers/search', [CareerController::class, 'search']);

// for Career By user
Route::get('/careers/my-careers', [CareerController::class, 'careerByUser']);

//   for all Careers
Route::get('/careers', [CareerController::class, 'all']);

//     for updating career
Route::put('/careers/update-career/{careerID}', [CareerController::class, 'update']);

// for delete career
Route::delete('/careers/delete-career/{careerID}', [CareerController::class, 'delete']);


//ADMIN routes
//list all user
Route::get('/admin/users', [UserController::class, 'allUsers']);

//add user by admin
Route::post('/admin/users/add-user', [UserController::class, 'addUser']);

// Edit user info
Route::put('/admin/users/edit-user/{userID}', [UserController::class, 'editUser']);

// Delete User
Route::delete('/admin/users/delete-user/{userID}', [UserController::class, 'deleteUser']);

Route::get('/admin/users/{id}', [UserController::class, 'getUserById']);
