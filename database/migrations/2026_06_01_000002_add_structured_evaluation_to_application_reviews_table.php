<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('application_reviews', 'overall_score')) {
                $table->decimal('overall_score', 5, 2)->nullable()->after('recommendation')->index();
            }

            if (! Schema::hasColumn('application_reviews', 'rubric_scores')) {
                $table->json('rubric_scores')->nullable()->after('overall_score');
            }

            if (! Schema::hasColumn('application_reviews', 'match_signals')) {
                $table->json('match_signals')->nullable()->after('rubric_scores');
            }

            if (! Schema::hasColumn('application_reviews', 'evaluated_snapshot_hash')) {
                $table->string('evaluated_snapshot_hash', 80)->nullable()->after('match_signals')->index();
            }

            if (! Schema::hasColumn('application_reviews', 'evaluated_snapshot_version')) {
                $table->unsignedInteger('evaluated_snapshot_version')->nullable()->after('evaluated_snapshot_hash');
            }

            if (! Schema::hasColumn('application_reviews', 'evaluated_at')) {
                $table->timestamp('evaluated_at')->nullable()->after('evaluated_snapshot_version');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_reviews', function (Blueprint $table) {
            foreach ([
                'evaluated_at',
                'evaluated_snapshot_version',
                'evaluated_snapshot_hash',
                'match_signals',
                'rubric_scores',
                'overall_score',
            ] as $column) {
                if (Schema::hasColumn('application_reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
