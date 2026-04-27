<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            // --- Primary Key ---
            $table->id();

            // --- العلاقات ---
            $table->foreignId('job_id')
                  ->constrained('jobs')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // --- منع التقديم المزدوج ---
            $table->unique(['job_id', 'user_id']);

            // --- بيانات التقديم ---
            $table->text('cover_letter')->nullable();    // رسالة التغطية
            $table->string('cv_path', 255)->nullable();  // مسار ملف CV
            $table->text('about')->nullable();           // نبذة عن المتقدم (من البيانات القديمة)

            // --- بيانات تُحفظ وقت التقديم (snapshot) ---
            // نحتفظ بها لأن بيانات المستخدم قد تتغير لاحقاً
            $table->string('applicant_name', 150)->nullable();
            $table->string('applicant_email', 150)->nullable();
            $table->string('applicant_phone', 20)->nullable();

            // --- حالة الطلب ---
            $table->enum('status', [
                'pending',      // في انتظار المراجعة
                'viewed',       // شاهدته الشركة
                'shortlisted',  // في القائمة المختصرة
                'interview',    // دُعي للمقابلة
                'accepted',     // قُبل
                'rejected',     // رُفض
            ])->default('pending');

            // --- ملاحظات الشركة ---
            $table->text('notes')->nullable();

            // --- التواريخ ---
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('applied_at')->useCurrent(); // وقت التقديم الفعلي
            $table->timestamps();
            $table->softDeletes();

            // --- Indexes ---
            $table->index('job_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
