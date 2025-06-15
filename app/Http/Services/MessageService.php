<?php

namespace App\Http\Services;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    public function sendMessage(int $receiverId, string $content): Message
    {
        return Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'content' => $content,
            'is_read' => false,
        ]);
    }

    public function getConversationWith(int $userId)
    {
        $authId = Auth::id();

        return Message::where(function ($q) use ($authId, $userId) {
            $q->where('sender_id', $authId)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($q) use ($authId, $userId) {
                $q->where('sender_id', $userId)
                    ->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get();
    }
}
