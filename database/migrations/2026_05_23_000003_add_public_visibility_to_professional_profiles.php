<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('professional_profiles')) {
            return;
        }

        Schema::table('professional_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('professional_profiles', 'profile_visibility')) {
                $table->string('profile_visibility', 40)->default('public')->after('open_to_work')->index();
            }

            if (! Schema::hasColumn('professional_profiles', 'public_sections')) {
                $table->json('public_sections')->nullable()->after('profile_visibility');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('professional_profiles')) {
            return;
        }

        Schema::table('professional_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('professional_profiles', 'public_sections')) {
                $table->dropColumn('public_sections');
            }

            if (Schema::hasColumn('professional_profiles', 'profile_visibility')) {
                $table->dropIndex(['profile_visibility']);
                $table->dropColumn('profile_visibility');
            }
        });
    }
};
