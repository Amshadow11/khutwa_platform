<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('application_activities', 'actor_type')) {
                $table->string('actor_type', 255)->nullable()->after('company_id');
            }

            if (! Schema::hasColumn('application_activities', 'actor_id')) {
                $table->unsignedBigInteger('actor_id')->nullable()->after('actor_type');
            }

            $table->index(['actor_type', 'actor_id'], 'application_activities_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('application_activities', function (Blueprint $table) {
            $table->dropIndex('application_activities_actor_idx');

            foreach (['actor_id', 'actor_type'] as $column) {
                if (Schema::hasColumn('application_activities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
