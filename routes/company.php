<?php

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

Route::middleware(['auth:company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {

        // ------------------------------------------------
        // Dashboard
        // ------------------------------------------------
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // ------------------------------------------------
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

        // تفعيل/تعطيل وظيفة — خارج Resource
        Route::patch('jobs/{job}/toggle', [JobController::class, 'toggle'])
            ->name('jobs.toggle');

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
