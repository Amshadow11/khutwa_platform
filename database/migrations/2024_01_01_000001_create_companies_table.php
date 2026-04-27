<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            // --- Primary Key ---
            $table->id();

            // --- معلومات أساسية ---
            $table->string('company_name', 200);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('phone_code', 10)->nullable()->default('YE');

            // --- الملف التعريفي ---
            $table->string('logo', 255)->nullable();
            $table->string('profile_picture', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('industry', 100)->nullable();
            $table->year('founded_year')->nullable();

            // --- حجم الشركة ---
            $table->enum('company_size', ['startup', 'small', 'medium', 'large'])
                  ->default('small');

            // --- الاشتراك ---
            $table->string('subscription_plan', 50)->default('free');
            $table->boolean('subscription')->default(false);
            $table->date('subscription_started')->nullable();
            $table->date('subscription_end')->nullable();

            // --- الحالة والتحقق ---
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->boolean('is_verified')->default(false);

            // --- إحصائيات ---
            $table->unsignedInteger('views')->default(0);

            // --- الأدوار ---
            // ثابتة دائماً 'company' — لكن نحتفظ بها للتوافق مع البيانات القديمة
            $table->string('role', 20)->default('company');

            // --- التواريخ ---
            $table->timestamp('last_login')->nullable();
            $table->timestamps(); // created_at + updated_at
            $table->softDeletes(); // للحذف الآمن بدلاً من الحذف النهائي

            // --- Indexes ---
            $table->index('status');
            $table->index('is_verified');
            $table->index('subscription_plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
