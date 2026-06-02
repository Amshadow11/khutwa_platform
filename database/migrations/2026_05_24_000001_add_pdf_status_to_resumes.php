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
            if (! Schema::hasColumn('resumes', 'pdf_status')) {
                $table->string('pdf_status', 40)->default('not_generated')->after('generated_pdf_path')->index();
            }

            if (! Schema::hasColumn('resumes', 'pdf_error')) {
                $table->text('pdf_error')->nullable()->after('pdf_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('resumes')) {
            return;
        }

        Schema::table('resumes', function (Blueprint $table) {
            if (Schema::hasColumn('resumes', 'pdf_status')) {
                $table->dropIndex(['pdf_status']);
                $table->dropColumn('pdf_status');
            }

            if (Schema::hasColumn('resumes', 'pdf_error')) {
                $table->dropColumn('pdf_error');
            }
        });
    }
};
