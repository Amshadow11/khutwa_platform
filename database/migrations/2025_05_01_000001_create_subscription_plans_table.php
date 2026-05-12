<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);              // Free, Basic, Pro, Enterprise
            $table->string('slug', 50)->unique();     // free, basic, pro, enterprise
            $table->text('description')->nullable();

            // السعر والدورة
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'yearly', 'lifetime'])->default('monthly');

            // Trial
            $table->unsignedInteger('trial_days')->default(0);

            // ترتيب العرض + الحالة
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // هل يظهر في صفحة الأسعار

            $table->timestamps();

            $table->index('slug');
            $table->index(['is_active', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};