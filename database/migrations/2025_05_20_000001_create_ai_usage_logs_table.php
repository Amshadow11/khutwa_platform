<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول تتبع استخدام AI.
     *
     * الأغراض:
     *   1. Rate Limiting — كم مرة استخدم المستخدم feature معينة هذا الشهر
     *   2. Cost Tracking — كم تكلّف كل طلب (tokens × price)
     *   3. Analytics — أكثر الميزات استخداماً
     *   4. Debugging — تتبع الأخطاء
     *
     * feature values:
     *   cover_letter, cv_parser, job_description,
     *   job_matching, screening_questions, auto_filtering, smart_search
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();

            // المستخدم أو الشركة (أحدهما فقط)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('company_id')
                  ->nullable()
                  ->constrained('companies')
                  ->nullOnDelete();

            // الميزة المستخدمة
            $table->string('feature', 50);

            // الشهر للـ Rate Limiting (YYYY-MM)
            $table->string('period', 7);

            // OpenAI usage
            $table->string('model', 50)->default('gpt-4o-mini');
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);

            // التكلفة بالدولار
            $table->decimal('cost_usd', 10, 6)->default(0);

            // الوقت المستغرق بالـ milliseconds
            $table->unsignedInteger('duration_ms')->nullable();

            // الحالة
            $table->string('status', 20)->default('success'); // success, failed, cached

            // رسالة الخطأ إذا فشل
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes للاستعلامات الشائعة
            $table->index(['user_id', 'feature', 'period']);
            $table->index(['company_id', 'feature', 'period']);
            $table->index(['feature', 'period']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};