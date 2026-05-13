<?php
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - لا تحتاج توكن
Route::post('/organizer-register', [AuthController::class, 'organizerRegister']);
Route::post('/exhibitor-register', [AuthController::class, 'exhibitorRegister']);
Route::post('/visitor-register', [AuthController::class, 'visitorRegister']);
Route::post('/login', [AuthController::class, 'login']);



// Protected routes - تحتاج توكن
Route::middleware('auth:sanctum')->group(function () {
Route::get('/user', function (Request $request) {
return $request->user();
});
Route::post('/logout', [AuthController::class, 'logout']);
});





//Route::middleware('role:admin')->group(function () {
//    Route::get('/users', [AdminController::class, 'getAllUsers']);
//    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
//    Route::get('/exhibitions', [AdminController::class, 'getAllExhibitions']);
//});
//Route::middleware('role:Organizer')->group(function () {
//    Route::post('/exhibitions', [OrganizerController::class, 'createExhibition']);
//    Route::put('/exhibitions/{id}', [OrganizerController::class, 'updateExhibition']);
//});
