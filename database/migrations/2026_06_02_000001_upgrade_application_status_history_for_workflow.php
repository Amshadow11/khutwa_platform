<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_status_history', function (Blueprint $table) {
            if (! Schema::hasColumn('application_status_history', 'from_status')) {
                $table->string('from_status', 40)->nullable()->after('application_id')->index();
            }

            if (! Schema::hasColumn('application_status_history', 'actor_type')) {
                $table->string('actor_type', 255)->nullable()->after('note');
            }

            if (! Schema::hasColumn('application_status_history', 'actor_id')) {
                $table->unsignedBigInteger('actor_id')->nullable()->after('actor_type');
            }

            if (! Schema::hasColumn('application_status_history', 'transition_key')) {
                $table->string('transition_key', 80)->nullable()->after('actor_id')->index();
            }

            if (! Schema::hasColumn('application_status_history', 'metadata')) {
                $table->json('metadata')->nullable()->after('transition_key');
            }

            $table->index(['application_id', 'changed_at'], 'ash_application_changed_idx');
            $table->index(['from_status', 'status'], 'ash_from_status_idx');
            $table->index(['actor_type', 'actor_id'], 'ash_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('application_status_history', function (Blueprint $table) {
            $table->dropIndex('ash_application_changed_idx');
            $table->dropIndex('ash_from_status_idx');
            $table->dropIndex('ash_actor_idx');

            foreach (['metadata', 'transition_key', 'actor_id', 'actor_type', 'from_status'] as $column) {
                if (Schema::hasColumn('application_status_history', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
