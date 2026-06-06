<?php

use App\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('applications', 'about')) {
            DB::table('applications')
                ->whereNull('cover_letter')
                ->whereNotNull('about')
                ->update(['cover_letter' => DB::raw('about')]);

            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('about');
            });
        }

        if (Schema::hasColumn('application_activities', 'company_id')) {
            DB::table('application_activities')
                ->whereNull('actor_type')
                ->whereNotNull('company_id')
                ->update([
                    'actor_type' => Company::class,
                    'actor_id' => DB::raw('company_id'),
                ]);

            Schema::table('application_activities', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('applications', 'about')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->text('about')->nullable()->after('cv_path');
            });
        }

        if (! Schema::hasColumn('application_activities', 'company_id')) {
            Schema::table('application_activities', function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->nullable()
                    ->after('application_id')
                    ->constrained('companies')
                    ->nullOnDelete();
            });

            DB::table('application_activities')
                ->where('actor_type', Company::class)
                ->whereNotNull('actor_id')
                ->update(['company_id' => DB::raw('actor_id')]);
        }
    }
};
