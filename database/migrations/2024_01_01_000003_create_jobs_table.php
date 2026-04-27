<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            // --- Primary Key ---
            $table->id();

            // --- العلاقات ---
            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete(); // إذا حُذفت الشركة تُحذف وظائفها

            // --- معلومات الوظيفة الأساسية ---
            $table->string('title', 255);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();

            // --- التصنيف والنوع ---
            // category: وظيفة أو تدريب (من البيانات الأصلية)
            $table->enum('category', ['job', 'training'])->default('job');
            // نوع الدوام
            $table->string('job_type', 100)->nullable(); // full_time, part_time, remote, contract, freelance
            $table->string('experience_level', 50)->nullable(); // مبتدئ، متوسط، خبير

            // --- الموقع ---
            $table->string('location', 150)->nullable();
            $table->boolean('remote_work')->default(false);

            // --- الراتب ---
            $table->string('salary', 100)->nullable();
            $table->string('salary_range', 100)->nullable();

            // --- الحالة ---
            $table->enum('status', ['active', 'inactive', 'expired', 'draft'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->boolean('urgent')->default(false);
            $table->date('deadline')->nullable(); // آخر موعد للتقديم

            // --- إحصائيات ---
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('views_count')->default(0); // للتوافق

            // --- التواريخ ---
            $table->timestamp('post_date')->useCurrent(); // من البيانات الأصلية
            $table->timestamps(); // created_at + updated_at
            $table->softDeletes();

            // --- Indexes ---
            $table->index('company_id');
            $table->index('status');
            $table->index('is_active');
            $table->index('category');
            $table->index('location');
            $table->index('job_type');
            $table->index('created_at'); // للترتيب الزمني
            $table->index(['is_active', 'status']); // Composite — الأكثر استخداماً في البحث
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
