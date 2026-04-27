<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // --- Primary Key ---
            $table->id();

            // --- معلومات أساسية ---
            $table->string('username', 100);
            $table->string('full_name', 150)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('phone_code', 10)->nullable()->default('YE');

            // --- الملف الشخصي ---
            $table->string('profile_picture', 255)->nullable();
            $table->string('profile_image', 255)->nullable();  // للتوافق مع البيانات القديمة
            $table->text('bio')->nullable();
            $table->string('address', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            // --- الروابط المهنية ---
            $table->string('linkedin_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('portfolio_url', 255)->nullable();

            // --- بيانات المهارات (نصية — سيتم تفكيكها لاحقاً في مرحلة 2) ---
            // الجداول المنفصلة user_skills / user_education / user_experience
            // ستُعامَل في المرحلة الثانية
            $table->text('skills')->nullable();
            $table->text('experience')->nullable();
            $table->text('education')->nullable();

            // --- الحالة والتحقق ---
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);

            // --- الأدوار ---
            $table->string('role', 30)->default('job_seeker');

            // --- التواريخ ---
            $table->timestamp('last_login')->nullable();
            $table->timestamps(); // created_at + updated_at
            $table->softDeletes();

            // --- Indexes ---
            $table->index('status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
