<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول البيانات المستخرجة من الـ CV.
     *
     * يُنشأ سجل لكل CV يُحلَّل.
     * البيانات تُستخدم في:
     *   - تعبئة الملف الشخصي تلقائياً
     *   - Job Matching
     *   - AI Screening
     */
    public function up(): void
    {
        Schema::create('cv_parsed_data', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // الطلب المرتبط (nullable — قد يُحلَّل CV بدون تقديم)
            $table->foreignId('application_id')
                  ->nullable()
                  ->constrained('applications')
                  ->nullOnDelete();

            // مسار ملف الـ CV
            $table->string('cv_path', 500)->nullable();

            // البيانات المستخرجة كـ JSON
            // البنية: {name, email, phone, skills[], experience[], education[], languages[], summary}
            $table->json('parsed_data')->nullable();

            // درجة الثقة بالاستخراج (0.0 - 1.0)
            $table->decimal('confidence_score', 3, 2)->nullable();

            // هل تمت تعبئة الملف الشخصي تلقائياً؟
            $table->boolean('profile_updated')->default(false);

            $table->timestamp('parsed_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_parsed_data');
    }
};