<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * إضافة stripe_price_id لجدول subscription_plans.
     *
     * كل خطة في Stripe لها Price object:
     *   - Free  → لا تحتاج Price (null)
     *   - Basic → price_1ABC...
     *   - Pro   → price_1DEF...
     *
     * يُملأ يدوياً من Stripe Dashboard أو من Seeder.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('stripe_price_id', 100)
                  ->nullable()
                  ->after('is_public')
                  ->comment('Stripe Price ID — يُملأ يدوياً من Stripe Dashboard');

            $table->index('stripe_price_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIndex(['stripe_price_id']);
            $table->dropColumn('stripe_price_id');
        });
    }
};