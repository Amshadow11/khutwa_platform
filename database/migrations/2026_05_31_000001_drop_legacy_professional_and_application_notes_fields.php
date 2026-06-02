<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['skills', 'experience', 'education'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'notes')) {
            $this->backfillLegacyApplicationNotes();

            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'skills')) {
                    $table->text('skills')->nullable();
                }
                if (! Schema::hasColumn('users', 'experience')) {
                    $table->text('experience')->nullable();
                }
                if (! Schema::hasColumn('users', 'education')) {
                    $table->text('education')->nullable();
                }
            });
        }

        if (Schema::hasTable('applications') && ! Schema::hasColumn('applications', 'notes')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->text('notes')->nullable();
            });
        }
    }

    private function backfillLegacyApplicationNotes(): void
    {
        if (! Schema::hasTable('application_notes') || ! Schema::hasTable('jobs')) {
            return;
        }

        DB::table('applications')
            ->join('jobs', 'jobs.id', '=', 'applications.job_id')
            ->whereNotNull('applications.notes')
            ->where('applications.notes', '!=', '')
            ->select([
                'applications.id as application_id',
                'applications.notes',
                'jobs.company_id',
            ])
            ->orderBy('applications.id')
            ->chunkById(100, function ($applications) {
                foreach ($applications as $application) {
                    $exists = DB::table('application_notes')
                        ->where('application_id', $application->application_id)
                        ->where('company_id', $application->company_id)
                        ->where('body', $application->notes)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('application_notes')->insert([
                        'application_id' => $application->application_id,
                        'company_id' => $application->company_id,
                        'body' => $application->notes,
                        'visibility' => 'internal',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }, 'applications.id', 'application_id');
    }
};
