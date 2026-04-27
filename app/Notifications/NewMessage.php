<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار عند استلام رسالة جديدة.
 */
class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
        private readonly Conversation $conversation
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sender  = $this->message->sender;
        $name    = method_exists($sender, 'getDisplayNameAttribute')
                    ? $sender->display_name
                    : ($sender->company_name ?? $sender->username ?? 'شخص ما');

        $preview = mb_substr($this->message->body, 0, 80);
        if (mb_strlen($this->message->body) > 80) {
            $preview .= '...';
        }

        return [
            'type'            => 'new_message',
            'message'         => "رسالة جديدة من {$name}: {$preview}",
            'conversation_id' => $this->conversation->id,
            'sender_name'     => $name,
            'preview'         => $preview,
            'url'             => route('messages.show', $this->conversation->id),
        ];
    }
}
