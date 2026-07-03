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

Route::middleware(['auth:sanctum', 'role:organizer'])
->group(function () {
    //عرض و تعديل الملف الشخصي للمنظم
    Route::get('/organizer-show', [ProfileController::class, 'organizerShow']);
    Route::put('/organizer-update', [ProfileController::class, 'organizerUpdate']);

    // Crud للمعارض للمنظم المسجل دخول حاليا فقط
    Route::get('/exhibitions', [ExhibitionController::class, 'index']);
    Route::get('/exhibitions/{id}', [ExhibitionController::class, 'show']);
    Route::post('/exhibitions', [ExhibitionController::class, 'store']);
    Route::put('/exhibitions/{id}', [ExhibitionController::class, 'update']);
    Route::delete('/exhibitions/{id}', [ExhibitionController::class, 'destroy']);

    Route::put('/exhibitions/{id}/publish', [ExhibitionController::class, 'publish']);
    Route::put('/exhibitions/{id}/start', [ExhibitionController::class, 'start']);
    Route::put('/exhibitions/{id}/complete', [ExhibitionController::class, 'complete']);
    Route::put('/exhibitions/{id}/cancel', [ExhibitionController::class, 'cancel']);

    // ادارة الاجنحة التابعة لمعرض محدد
    Route::post('/exhibitions/{exhibitionId}/booths', [BoothController::class, 'store']);
    Route::get('/exhibitions/{exhibitionId}/booths', [BoothController::class, 'index']);
    Route::get('/booths/{boothId}', [BoothController::class, 'show']);
    Route::put('/booths/{boothId}', [BoothController::class, 'update']);
    Route::delete('/booths/{boothId}', [BoothController::class, 'destroy']);



});

Route::middleware(['auth:sanctum', 'role:admin'])
->prefix('admin')
->group(function () {
    Route::get('/exhibitions',        [ExhibitionController::class, 'list']);
    Route::get('/exhibitions/{id}',   [ExhibitionController::class, 'read']);
    Route::put('/exhibitions/{id}',   [ExhibitionController::class, 'edit']);
    Route::delete('/exhibitions/{id}',[ExhibitionController::class, 'delete']);
});
