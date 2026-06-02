<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('username', 100);
            $table->string('full_name', 150)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('phone', 20)->nullable();
            $table->string('phone_code', 10)->nullable()->default('YE');

            $table->string('profile_picture', 255)->nullable();
            $table->string('profile_image', 255)->nullable();
            $table->text('bio')->nullable();
            $table->string('address', 255)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();

            $table->string('linkedin_url', 255)->nullable();
            $table->string('github_url', 255)->nullable();
            $table->string('portfolio_url', 255)->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);
            $table->string('role', 30)->default('job_seeker');
            $table->timestamp('last_login')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
