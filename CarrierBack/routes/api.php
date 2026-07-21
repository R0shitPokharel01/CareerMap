<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\Career\CareerAiController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\NotificationController;
// for login
Route::post('/login', [AuthController::class, 'login']);

//  for register
Route::post('/register', [AuthController::class, 'register']);

Route::get('/reset-password/{token}', function (string $token) {
    return response()->json([
        'token' => $token,
        'email' => request('email'),
    ]);
})->name('password.reset');


Route::post('/forgot-password', [ForgotPasswordController::class, 'forgotPassword']);

Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    //  for logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // For generating new  career by admin
    Route::post('/new-career', [CareerAiController::class, 'newCareer']);




    //  for career search
    Route::get('/careers/search', [CareerController::class, 'search']);

    // for Career By user
    Route::get('/careers/my-careers', [CareerController::class, 'careerByUser']);

    // for delete career
    Route::delete('/careers/delete-career/{careerID}', [CareerController::class, 'delete']);


    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);


    //ADMIN routes
    Route::middleware('role:admin')->group(function () {

        //list all user
        Route::get('/admin/users', [UserController::class, 'allUsers']);

        //add user by admin
        Route::post('/admin/users/add-user', [UserController::class, 'addUser']);

        // Edit user info
        Route::put('/admin/users/edit-user/{userID}', [UserController::class, 'editUser']);

        // Delete User
        Route::delete('/admin/users/delete-user/{userID}', [UserController::class, 'deleteUser']);

        //get user by id
        Route::get('/admin/users/{id}', [UserController::class, 'getUserById']);
    });
});



//   for all Careers
Route::get('/careers', [CareerController::class, 'all']);

//     for updating career
Route::put('/careers/update-career/{careerID}', [CareerController::class, 'update']);
