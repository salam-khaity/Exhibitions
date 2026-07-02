<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes - لا تحتاج توكن
Route::post('/organizer-register', [AuthController::class, 'organizerRegister']);
Route::post('/exhibitor-register', [AuthController::class, 'exhibitorRegister']);
Route::post('/visitor-register', [AuthController::class, 'visitorRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - تحتاج توكن
Route::middleware('auth:sanctum')->group(function () {
Route::get('/user', function (Request $request) {
        return $request->user();});
Route::post('/logout', [AuthController::class, 'logout']);
});

//عرض و تعديل الملف الشخصي للمنظم 
Route::middleware(['auth:sanctum', 'role:organizer'])
->group(function () {
    Route::get('/organizer-show', [ProfileController::class, 'organizerShow']);
    Route::put('/organizer-update', [ProfileController::class, 'organizerUpdate']);
});
