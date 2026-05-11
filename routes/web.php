<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\User\ApplicationController as UserApplicationController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shared\MessageController;
use App\Http\Controllers\Shared\NotificationController;
use Illuminate\Support\Facades\Broadcast;

// ============================================================
// المسارات العامة (بدون تسجيل دخول)
// ============================================================

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [HomeController::class, 'search'])->name('search');

Route::get('/jobs', [JobsController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [JobsController::class, 'show'])->name('jobs.show');

// ============================================================
// Auth
// ============================================================

Route::middleware('guest:company,web')->group(function () {
    Route::get('/login',  [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit')
        ->middleware('throttle:5,1');

    Route::get('/register',          [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register/user',    [RegisterController::class, 'registerUser'])->name('register.user');
    Route::post('/register/company', [RegisterController::class, 'registerCompany'])->name('register.company');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm']
    )->name('password.request');

    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink']
    )->name('password.email')
     ->middleware('throttle:3,1');

    // --------------------------------------------------------
    // إعادة تعيين كلمة المرور — للمستخدم
    // {token} يصل عبر رابط الإيميل
    // --------------------------------------------------------
    Route::get('/reset-password/{token}',[ResetPasswordController::class, 'showForm']
    )->name('password.reset');

    Route::post('/reset-password',  [ResetPasswordController::class, 'reset']
    )->name('password.update');
});
Route::middleware('auth:web')->group(function () {

    // صفحة "تحقق من بريدك" — تظهر للمستخدم غير المتحقق
    Route::get('/email/verify',
        [VerificationController::class, 'notice']
    )->name('verification.notice');

    // معالجة رابط التحقق من الإيميل (يصل عبر URL موقَّع)
    Route::get('/email/verify/{id}/{hash}',
        [VerificationController::class, 'verify']
    )->middleware(['signed', 'throttle:6,1'])
     ->name('verification.verify');

    // إعادة إرسال إيميل التحقق
    Route::post('/email/verification-notification',
        [VerificationController::class, 'resend']
    )->middleware('throttle:3,1')
     ->name('verification.send');
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ============================================================
// مسارات الشركة
// ============================================================

require __DIR__ . '/company.php';

// ============================================================
// مسارات المستخدم (باحث العمل)
// ============================================================

Route::middleware(['auth:web'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');

        // طلبات التقديم
        Route::get('/applications',                 [UserApplicationController::class, 'index'])
            ->name('applications.index');
        Route::get('/applications/{application}',   [UserApplicationController::class, 'show'])
            ->name('applications.show');
        Route::delete('/applications/{application}',[UserApplicationController::class, 'destroy'])
            ->name('applications.destroy');

        // الملف الشخصي
        Route::get('/profile',          [UserProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit',     [UserProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile',          [UserProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [UserProfileController::class, 'changePassword'])->name('profile.password');
    });

// التقديم على وظيفة (POST من صفحة الوظيفة)
Route::middleware(['auth:web'])
    ->post('/apply/{job}', [UserApplicationController::class, 'store'])
    ->name('jobs.apply');

// ============================================================
// الرسائل والإشعارات — يعمل للشركة والمستخدم معاً
// ============================================================
Route::middleware(['auth:company,web'])->group(function () {

    // الرسائل
    Route::get('/messages',                       [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}',        [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/start',                [MessageController::class, 'start'])->name('messages.start');
    Route::post('/messages/{conversation}/send',  [MessageController::class, 'send'])->name('messages.send');

    // الإشعارات
    Route::get('/notifications',                  [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}',             [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/mark-all',        [NotificationController::class, 'markAll'])->name('notifications.markAll');
});
// Broadcasting Auth — يدعم كلا الـ Guards
Broadcast::routes(['middleware' => ['auth:web,company']]);