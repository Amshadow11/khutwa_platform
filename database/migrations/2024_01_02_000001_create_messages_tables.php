<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // جدول المحادثات (Conversations)
        // كل محادثة بين شركة ومستخدم حول وظيفة معينة
        // ============================================================
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                  ->constrained('companies')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // الوظيفة المرتبطة بالمحادثة (اختيارية)
            $table->foreignId('job_id')
                  ->nullable()
                  ->constrained('jobs')
                  ->nullOnDelete();

            // آخر رسالة للعرض السريع
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();

            // عدد الرسائل غير المقروءة لكل طرف
            $table->unsignedInteger('company_unread')->default(0);
            $table->unsignedInteger('user_unread')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // محادثة واحدة فقط بين شركة ومستخدم لكل وظيفة
            $table->unique(['company_id', 'user_id', 'job_id']);

            $table->index('company_id');
            $table->index('user_id');
            $table->index('last_message_at');
        });

        // ============================================================
        // جدول الرسائل
        // ============================================================
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            // المرسل: إما شركة أو مستخدم (Polymorphic)
            $table->string('sender_type');  // App\Models\Company أو App\Models\User
            $table->unsignedBigInteger('sender_id');
            $table->index(['sender_type', 'sender_id']);

            $table->text('body');

            // مرفق (اختياري)
            $table->string('attachment_path', 255)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->string('attachment_type', 50)->nullable();

            // حالة القراءة
            $table->timestamp('read_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('conversation_id');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
