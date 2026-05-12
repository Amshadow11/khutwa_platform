<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * feature_key المتاحة:
     *   max_jobs_per_month  → عدد الوظائف المسموح بها شهرياً (-1 = غير محدود)
     *   featured_jobs       → عدد الوظائف المميزة شهرياً
     *   urgent_jobs         → هل يسمح بـ urgent (true/false)
     *   analytics           → إحصائيات متقدمة (true/false)
     *   ai_matching         → AI Matching (true/false)
     *   api_access          → وصول API (true/false)
     *   team_members        → عدد أعضاء الفريق
     *   messaging_limit     → حد الرسائل شهرياً (-1 = غير محدود)
     *   cv_downloads        → عدد CVs قابلة للتحميل شهرياً (-1 = غير محدود)
     */
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();

            $table->foreignId('plan_id')
                  ->constrained('subscription_plans')
                  ->cascadeOnDelete();

            $table->string('feature_key', 100);   // max_jobs_per_month
            $table->string('feature_value', 255);  // 5 أو true أو -1

            $table->timestamps();

            // كل feature مرة واحدة لكل خطة
            $table->unique(['plan_id', 'feature_key']);
            $table->index('feature_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};