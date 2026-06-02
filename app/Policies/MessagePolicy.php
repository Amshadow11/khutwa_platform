<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function view(object $actor, Message $message): bool
    {
        $conversation = $message->conversation;

        if ($actor instanceof Company) {
            return (int) $conversation?->company_id === (int) $actor->id;
        }

        if ($actor instanceof User) {
            return (int) $conversation?->user_id === (int) $actor->id;
        }

        return false;
    }

    public function downloadAttachment(object $actor, Message $message): bool
    {
        return $this->view($actor, $message);
    }
}
