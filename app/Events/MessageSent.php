<?php

namespace App\Events;

use App\Models\Message;
use App\Models\Company;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Message $message
    ) {}

    /**
     * يُبث على Channel خاص بالمحادثة.
     * Private Channel — يتطلب تسجيل دخول للاشتراك.
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("conversation.{$this->message->conversation_id}");
    }

    /**
     * البيانات التي تُرسل للـ Client.
     */
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'body'            => $this->message->body,
            'sender_id'       => $this->message->sender_id,
            'sender_type'     => $this->message->sender_type,
            'is_from_company' => $this->message->sender_type === Company::class,
            'sender_name'     => $this->message->sender_type === Company::class
                                    ? $this->message->sender?->company_name
                                    : $this->message->sender?->display_name,
            'attachment_url'  => $this->message->attachment_url,
            'attachment_name' => $this->message->attachment_name,
            'created_at'      => $this->message->created_at->format('h:i A'),
        ];
    }

    /**
     * اسم الـ Event في الـ Client.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}