<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laravel Database Notifications — الجدول الرسمي للإشعارات.
     * يُستخدم مع: $user->notify(new SomeNotification())
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // الجهة المستقبِلة (Polymorphic — يستقبل شركات ومستخدمين)
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->index(['notifiable_type', 'notifiable_id']);

            // نوع الإشعار (اسم الـ Class)
            $table->string('type');

            // بيانات الإشعار (JSON)
            $table->text('data');

            // وقت القراءة — null = غير مقروء
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
