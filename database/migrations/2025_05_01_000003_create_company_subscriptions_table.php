<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->foreignId('plan_id')
                  ->constrained('subscription_plans');

            $table->enum('status', [
                'active',
                'trial',
                'cancelled',
                'expired',
                'pending',
            ])->default('active');

            // فترة الاشتراك
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();      // null = لا ينتهي
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // بيانات الدفع (للمستقبل)
            $table->string('payment_method', 50)->nullable();   // stripe, manual, etc
            $table->string('payment_reference', 255)->nullable(); // transaction ID
            $table->decimal('amount_paid', 10, 2)->default(0);

            // ملاحظات الأدمن
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscriptions');
    }
};
