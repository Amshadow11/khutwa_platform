<?php

use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Channel الإشعارات الخاص بكل مستخدم
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

// Channel الإشعارات الخاص بكل شركة
Broadcast::channel('App.Models.Company.{id}', function (Company $company, int $id) {
    return (int) $company->id === $id;
});

// Channel المحادثة — Private
// يسمح فقط للمستخدم أو الشركة المشاركة في المحادثة
Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    // إذا كان المستخدم شركة
    if ($user instanceof Company) {
        return (int) $conversation->company_id === (int) $user->id;
    }

    // إذا كان المستخدم باحث عمل
    if ($user instanceof User) {
        return (int) $conversation->user_id === (int) $user->id;
    }

    return false;
});