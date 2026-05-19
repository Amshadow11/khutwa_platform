<?php

use App\Http\Controllers\Stripe\CheckoutController;
use App\Http\Controllers\Stripe\WebhookController;
use Illuminate\Support\Facades\Route;

// ============================================================
// Stripe Checkout Routes — تتطلب تسجيل دخول كشركة
// ============================================================

Route::middleware(['auth:company'])
    ->prefix('stripe')
    ->name('stripe.')
    ->group(function () {

        // إنشاء Checkout Session + توجيه للدفع
        Route::post('/checkout', [CheckoutController::class, 'create'])
            ->name('checkout.create');

        // Stripe يوجّه هنا بعد نجاح الدفع
        Route::get('/checkout/success', [CheckoutController::class, 'success'])
            ->name('checkout.success');

        // Stripe يوجّه هنا عند إلغاء الدفع
        Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])
            ->name('checkout.cancel');
    });

// ============================================================
// Stripe Webhook — بدون Auth (Stripe يُرسل مباشرة)
// بدون CSRF (راجع bootstrap/app.php)
// ============================================================

Route::post('/stripe/webhook', [WebhookController::class, 'handle'])
    ->name('stripe.webhook');