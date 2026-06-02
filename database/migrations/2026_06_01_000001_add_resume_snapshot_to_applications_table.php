<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'resume_id')) {
                $table->foreignId('resume_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('resumes')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('applications', 'resume_snapshot')) {
                $table->json('resume_snapshot')->nullable()->after('cv_path');
            }

            if (! Schema::hasColumn('applications', 'resume_snapshot_hash')) {
                $table->string('resume_snapshot_hash', 80)->nullable()->after('resume_snapshot')->index();
            }

            if (! Schema::hasColumn('applications', 'resume_snapshot_version')) {
                $table->unsignedInteger('resume_snapshot_version')->default(1)->after('resume_snapshot_hash');
            }

            if (! Schema::hasColumn('applications', 'resume_snapshot_created_at')) {
                $table->timestamp('resume_snapshot_created_at')->nullable()->after('resume_snapshot_version');
            }

            if (! Schema::hasColumn('applications', 'submitted_resume_pdf_path')) {
                $table->string('submitted_resume_pdf_path', 500)->nullable()->after('resume_snapshot_created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            foreach ([
                'submitted_resume_pdf_path',
                'resume_snapshot_created_at',
                'resume_snapshot_version',
                'resume_snapshot_hash',
                'resume_snapshot',
            ] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('applications', 'resume_id')) {
                $table->dropConstrainedForeignId('resume_id');
            }
        });
    }
};
