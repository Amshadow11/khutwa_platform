<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->text('body');
            $table->string('visibility', 40)->default('internal');
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
        });

        Schema::create('application_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('recommendation', 40)->nullable();
            $table->text('strengths')->nullable();
            $table->text('concerns')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'company_id']);
            $table->index(['rating', 'recommendation']);
        });

        Schema::create('application_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->string('location_type', 40)->default('online');
            $table->string('location', 255)->nullable();
            $table->string('meeting_url', 500)->nullable();
            $table->string('status', 40)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'scheduled_at']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('application_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('type', 80);
            $table->string('description', 500);
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['application_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_activities');
        Schema::dropIfExists('application_interviews');
        Schema::dropIfExists('application_reviews');
        Schema::dropIfExists('application_notes');
    }
};
