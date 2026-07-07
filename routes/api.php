<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExhibitionController;
use App\Http\Controllers\BoothController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VisitorController;
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
    Route::post('/organizer-update', [ProfileController::class, 'organizerUpdate']);

    // Crud للمعارض للمنظم المسجل دخول حاليا فقط
    Route::get('/exhibitions', [ExhibitionController::class, 'index']);
    Route::get('/exhibitions/{id}', [ExhibitionController::class, 'show']);
    Route::post('/exhibitions', [ExhibitionController::class, 'store']);
    Route::put('/exhibitions/{id}', [ExhibitionController::class, 'update']);
    Route::delete('/exhibitions/{id}', [ExhibitionController::class, 'destroy']);

    // إدارة الصور
    Route::post('/exhibitions/{exhibitionId}/images', [ExhibitionController::class, 'addImages']);
    Route::delete('/exhibitions/{exhibitionId}/images/{imageId}', [ExhibitionController::class, 'deleteImage']);

    // حالات المعرض
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

    Route::post('/booths/{boothId}/images', [BoothController::class, 'addImages']);
    Route::delete('/booths/{boothId}/images/{imageId}', [BoothController::class, 'deleteImage']);

// ── طلبات الحجز للمنظم ──
    Route::get('/booth-requests', [BoothController::class, 'indexRequest']);
    Route::put('/booths/{boothId}/approve', [BoothController::class, 'approve']);
    Route::put('/booths/{boothId}/reject', [BoothController::class, 'reject']);

    // إدارة الزوار
    Route::get('/exhibitions/{id}/visitors', [VisitorController::class, 'index']);
    Route::get('/exhibitions/{id}/visitors/export', [VisitorController::class, 'export']);

    // الإحصائيات
    Route::get('/statistic', [VisitorController::class, 'statistics']);
    Route::get('/exhibitions/{id}/statistics', [VisitorController::class, 'show']);

});

Route::middleware(['auth:sanctum', 'role:admin'])
->group(function () {

    Route::get('/admin-show', [ProfileController::class, 'adminShow']);
    Route::put('/admin-update', [ProfileController::class, 'adminUpdate']);
//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // المنظمون
    Route::get('/organizers', [AdminController::class, 'indexOrganizers']);
    Route::get('/organizers/{id}', [AdminController::class, 'showOrganizer']);
    Route::put('/organizers/{id}', [AdminController::class, 'updateOrganizer']);
    Route::put('/organizers/{id}/deactivate', [AdminController::class, 'deactivateOrganizer']);
    Route::put('/organizers/{id}/activate', [AdminController::class, 'activateOrganizer']);
    Route::delete('/organizers/{id}', [AdminController::class, 'deleteOrganizer']);
//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // العارضون
    Route::get('/exhibitors', [AdminController::class, 'indexExhibitors']);
    Route::get('/exhibitors/{id}', [AdminController::class, 'showExhibitor']);
    Route::put('/exhibitors/{id}', [AdminController::class, 'updateExhibitor']);
    Route::put('/exhibitors/{id}/deactivate', [AdminController::class, 'deactivateExhibitor']);
    Route::put('/exhibitors/{id}/activate', [AdminController::class, 'activateExhibitor']);
    Route::delete('/exhibitors/{id}', [AdminController::class, 'deleteExhibitor']);
//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // الزوار
    Route::get('/visitors', [AdminController::class, 'indexVisitors']);
    Route::get('/visitors/{id}', [AdminController::class, 'showVisitor']);
    Route::put('/visitors/{id}', [AdminController::class, 'updateVisitor']);
    Route::put('/visitors/{id}/deactivate', [AdminController::class, 'deactivateVisitor']);
    Route::put('/visitors/{id}/activate', [AdminController::class, 'activateVisitor']);
    Route::delete('/visitors/{id}', [AdminController::class, 'deleteVisitor']);
//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // المعارض
    Route::get('/exhibition', [AdminController::class, 'index']);
    Route::get('/exhibition/{id}', [AdminController::class, 'show']);
    Route::put('/exhibition/{id}', [AdminController::class, 'update']);
    Route::put('/exhibition/{id}/cancel', [AdminController::class, 'cancel']);
    Route::delete('/exhibition/{id}', [AdminController::class, 'destroy']);
//ــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــــ
    // الإحصائيات
    Route::get('/statistics', [AdminController::class, 'statistics']);

});

Route::middleware(['auth:sanctum', 'role:visitor'])
->group(function () {

    // الملف الشخصي
    Route::get('/visitor-show', [ProfileController::class, 'visitorShow']);
    Route::put('/visitor-update', [ProfileController::class, 'visitorUpdate']);
    Route::post('/visitor-avatar', [ProfileController::class, 'updateAvatar']);

    // تصفح المعارض
    Route::get('/exhibitions', [VisitorController::class, 'index']);
    Route::get('/exhibitions/{id}', [VisitorController::class, 'show']);

    // التذاكر والتسجيل
    Route::post('/exhibitions/{id}/register', [TicketController::class, 'register']);
    Route::get('/tickets', [TicketController::class, 'myTickets']);
    Route::get('/tickets/{id}', [TicketController::class, 'showTicket']);
    Route::delete('/tickets/{id}', [TicketController::class, 'cancelTicket']);
});

