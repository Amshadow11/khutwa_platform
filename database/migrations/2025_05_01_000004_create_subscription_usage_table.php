<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * يتتبع استهلاك كل شركة لكل feature شهرياً.
     *
     * مثال:
     *   company_id=1, feature_key=max_jobs_per_month, used=3, period=2026-05
     *   company_id=1, feature_key=featured_jobs,      used=1, period=2026-05
     */
    public function up(): void
    {
        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->string('feature_key', 100);
            $table->unsignedInteger('used')->default(0);
            $table->string('period', 7);  // 2026-05 (YYYY-MM)

            $table->timestamps();

            // منع تكرار نفس الـ feature في نفس الشهر لنفس الشركة
            $table->unique(['company_id', 'feature_key', 'period']);
            $table->index(['company_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usage');
    }
};