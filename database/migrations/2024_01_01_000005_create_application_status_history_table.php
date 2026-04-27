<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * جدول تاريخ تغيير حالة الطلبات.
     * يسجّل كل تغيير حالة مع الوقت والملاحظة — مهم لإظهار timeline للمتقدم.
     */
    public function up(): void
    {
        Schema::create('application_status_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                  ->constrained('applications')
                  ->cascadeOnDelete();

            $table->enum('status', [
                'pending', 'viewed', 'shortlisted',
                'interview', 'accepted', 'rejected',
            ]);

            $table->text('note')->nullable(); // ملاحظة اختيارية من الشركة عند التغيير

            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            // Index للاستعلام الأكثر استخداماً: تاريخ طلب معين
            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_history');
    }
};
