<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_match_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->nullableMorphs('initiated_by');
            $table->string('status', 40)->default('queued');
            $table->string('provider', 40)->nullable();
            $table->string('model', 80)->nullable();
            $table->unsignedInteger('matching_version')->default(1);
            $table->string('job_snapshot_hash', 80);
            $table->unsignedInteger('applications_total')->default(0);
            $table->unsignedInteger('applications_processed')->default(0);
            $table->unsignedInteger('applications_reused')->default(0);
            $table->unsignedInteger('applications_failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['job_id', 'status', 'created_at']);
            $table->index(['company_id', 'status', 'created_at']);
            $table->index(['job_snapshot_hash', 'matching_version']);
        });

        Schema::create('job_application_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_match_run_id')->constrained('job_match_runs')->cascadeOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('resume_snapshot_hash', 80)->nullable();
            $table->unsignedInteger('resume_snapshot_version')->default(1);
            $table->string('job_snapshot_hash', 80);
            $table->unsignedInteger('matching_version')->default(1);
            $table->string('match_cache_key', 128);
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->decimal('skills_score', 5, 2)->nullable();
            $table->decimal('experience_score', 5, 2)->nullable();
            $table->decimal('education_score', 5, 2)->nullable();
            $table->decimal('location_score', 5, 2)->nullable();
            $table->decimal('seniority_score', 5, 2)->nullable();
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('evidence')->nullable();
            $table->json('risk_flags')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->string('status', 40)->default('completed');
            $table->boolean('is_reused')->default(false);
            $table->foreignId('reused_from_match_id')->nullable()->constrained('job_application_matches')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['job_match_run_id', 'application_id']);
            $table->index(['match_cache_key', 'status']);
            $table->index(['job_id', 'overall_score']);
            $table->index(['company_id', 'job_id', 'overall_score']);
            $table->index(['application_id', 'evaluated_at']);
            $table->index(['resume_snapshot_hash', 'resume_snapshot_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_matches');
        Schema::dropIfExists('job_match_runs');
    }
};
