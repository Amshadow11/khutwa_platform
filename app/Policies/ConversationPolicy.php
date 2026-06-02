<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    public function view(object $actor, Conversation $conversation): bool
    {
        if ($actor instanceof Company) {
            return (int) $conversation->company_id === (int) $actor->id;
        }

        if ($actor instanceof User) {
            return (int) $conversation->user_id === (int) $actor->id;
        }

        return false;
    }

    public function create(object $actor): bool
    {
        if ($actor instanceof User) {
            return $actor->hasVerifiedEmail() && $actor->is_active && $actor->status === 'active';
        }

        if ($actor instanceof Company) {
            return $actor->is_verified && $actor->status === 'active';
        }

        return false;
    }
}
