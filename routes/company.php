<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Company\ApplicationController;
use App\Http\Controllers\Company\ATSController;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\JobController;
use App\Http\Controllers\Company\JobMatchController;
use App\Http\Controllers\Company\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:company,web')->group(function () {
    Route::get('/company/forgot-password', [ForgotPasswordController::class, 'showForm'])
        ->defaults('type', 'company')
        ->name('company.password.request');

    Route::post('/company/forgot-password', [ForgotPasswordController::class, 'sendLink'])
        ->defaults('type', 'company')
        ->name('company.password.email')
        ->middleware('throttle:3,1');

    Route::get('/company/reset-password/{token}', [ResetPasswordController::class, 'showForm'])
        ->defaults('type', 'company')
        ->name('company.password.reset');

    Route::post('/company/reset-password', [ResetPasswordController::class, 'reset'])
        ->defaults('type', 'company')
        ->name('company.password.update');
});

Route::middleware(['auth:company'])
    ->prefix('company')
    ->name('company.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('jobs', JobController::class);

        Route::middleware(['company.verified'])->group(function () {
            Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');
            Route::post('/jobs', [JobController::class, 'store'])->name('jobs.store');
            Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->name('jobs.edit');
            Route::put('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
            Route::delete('/jobs/{job}', [JobController::class, 'destroy'])->name('jobs.destroy');
            Route::patch('/jobs/{job}/toggle', [JobController::class, 'toggle'])->name('jobs.toggle');
            Route::post('/jobs/{job}/matches/run', [JobMatchController::class, 'store'])->name('jobs.matches.run');

            Route::patch('/applications/{application}/status', [ApplicationController::class, 'transitionStatus'])
                ->name('applications.transitionStatus');
            Route::post('/applications/{application}/notes', [ATSController::class, 'storeNote'])
                ->name('applications.notes.store');
            Route::post('/applications/{application}/review', [ATSController::class, 'review'])
                ->name('applications.review');
            Route::post('/applications/{application}/interviews', [ATSController::class, 'scheduleInterview'])
                ->name('applications.interviews.store');
            Route::get('/matches', [JobMatchController::class, 'index'])
                ->name('matches.index');
        });

        Route::get('/applications', [ApplicationController::class, 'index'])
            ->name('applications.index');

        Route::get('/applications/pipeline', [ApplicationController::class, 'pipeline'])
            ->name('applications.pipeline');

        Route::get('/applications/{application}', [ApplicationController::class, 'show'])
            ->name('applications.show');

        Route::get('/subscription', [SubscriptionController::class, 'index'])
            ->name('subscription.index');
        Route::post('/subscription/request', [SubscriptionController::class, 'requestUpgrade'])
            ->name('subscription.request');
        Route::delete('/subscription/request/{upgradeRequest}/cancel', [SubscriptionController::class, 'cancelRequest'])
            ->name('subscription.cancel');
        Route::post('/subscription/trial', [SubscriptionController::class, 'startTrial'])
            ->name('subscription.trial');
    });
