<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->index(['job_id', 'status', 'applied_at'], 'applications_job_status_applied_idx');
            $table->index(['user_id', 'status', 'applied_at'], 'applications_user_status_applied_idx');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_job_status_applied_idx');
            $table->dropIndex('applications_user_status_applied_idx');
        });
    }
};
