<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('resumes')) {
            return;
        }

        Schema::table('resumes', function (Blueprint $table) {
            if (! Schema::hasColumn('resumes', 'target_job_id')) {
                $table->foreignId('target_job_id')->nullable()->after('parent_resume_id')->constrained('jobs')->nullOnDelete();
            }

            if (! Schema::hasColumn('resumes', 'tailored_summary')) {
                $table->text('tailored_summary')->nullable()->after('direction');
            }

            if (! Schema::hasColumn('resumes', 'snapshot_version')) {
                $table->unsignedInteger('snapshot_version')->default(1)->after('profile_snapshot');
            }

            if (! Schema::hasColumn('resumes', 'snapshot_hash')) {
                $table->string('snapshot_hash', 80)->nullable()->after('snapshot_version')->index();
            }

            if (! Schema::hasColumn('resumes', 'snapshot_created_at')) {
                $table->timestamp('snapshot_created_at')->nullable()->after('snapshot_hash');
            }

            if (! Schema::hasColumn('resumes', 'render_metadata')) {
                $table->json('render_metadata')->nullable()->after('generated_pdf_path');
            }

            if (! Schema::hasColumn('resumes', 'private_share_token')) {
                $table->string('private_share_token', 80)->nullable()->unique()->after('public_token');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('resumes')) {
            return;
        }

        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'target_job_id')) {
                $table->dropForeign(['target_job_id']);
            }

            foreach (['target_job_id', 'tailored_summary', 'snapshot_version', 'snapshot_hash', 'snapshot_created_at', 'render_metadata', 'private_share_token'] as $column) {
                if (Schema::hasColumn('resumes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
