<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\JobController;
use Illuminate\Support\Facades\Route;

// ============================================================
// مسارات الشركة — محمية بـ Guard خاص
// ============================================================
// جميع هذه المسارات:
//   1. تتحقق من تسجيل الدخول كشركة (EnsureCompanyAuthenticated)
//   2. تبدأ بـ /company/...
//   3. لها prefix في أسماء المسارات: company.*
// ============================================================
Route::middleware('guest:company,web')->group(function () {

    Route::get('/company/forgot-password',[ForgotPasswordController::class, 'showForm']
    )->defaults('type', 'company')
     ->name('company.password.request');

    Route::post('/company/forgot-password', [ForgotPasswordController::class, 'sendLink']
    )->defaults('type', 'company')
     ->name('company.password.email')
     ->middleware('throttle:3,1');

    Route::get('/company/reset-password/{token}', [ResetPasswordController::class, 'showForm']
    )->defaults('type', 'company')
     ->name('company.password.reset');

    Route::post('/company/reset-password', [ResetPasswordController::class, 'reset']
    )->defaults('type', 'company')
     ->name('company.password.update');
});
Route::middleware(['auth:company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        // ------------------------------------------------
        // Dashboard
        // ------------------------------------------------
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
            
        // الوظائف — Resource Routes كاملة
        // ------------------------------------------------
        // index   → GET    /company/jobs           → company.jobs.index
        // create  → GET    /company/jobs/create    → company.jobs.create
        // store   → POST   /company/jobs           → company.jobs.store
        // show    → GET    /company/jobs/{job}     → company.jobs.show
        // edit    → GET    /company/jobs/{job}/edit → company.jobs.edit
        // update  → PUT    /company/jobs/{job}     → company.jobs.update
        // destroy → DELETE /company/jobs/{job}     → company.jobs.destroy
        Route::resource('jobs', JobController::class);
        Route::middleware(['company.verified'])->group(function () {
            Route::get('/jobs/create',        [JobController::class, 'create'])->name('jobs.create');
            Route::post('/jobs',              [JobController::class, 'store'])->name('jobs.store');
            Route::get('/jobs/{job}/edit',    [JobController::class, 'edit'])->name('jobs.edit');
            Route::put('/jobs/{job}',         [JobController::class, 'update'])->name('jobs.update');
            Route::delete('/jobs/{job}',      [JobController::class, 'destroy'])->name('jobs.destroy');
            Route::patch('/jobs/{job}/toggle',[JobController::class, 'toggle'])->name('jobs.toggle');
        });
        // تفعيل/تعطيل وظيفة — خارج Resource
        // ------------------------------------------------
        // طلبات التقديم الواردة
        // ------------------------------------------------
        Route::get('/applications', [ApplicationController::class, 'index'])
            ->name('applications.index');

        Route::get('/applications/{application}', [ApplicationController::class, 'show'])
            ->name('applications.show');

        Route::patch('/applications/{application}/status', [ApplicationController::class, 'updateStatus'])
            ->name('applications.updateStatus');
    });
