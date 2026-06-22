<?php

namespace App\Services;

use App\Models\ChatThread;
use App\Models\MessageAudit;
use App\Models\User;

class AuditService
{
    public function log(?ChatThread $thread, ?User $user, string $sender, string $message, array $meta = [], ?string $recipient = null, ?string $senderName = null): void
    {
        MessageAudit::create([
            'thread_id' => $thread?->id,
            'user_id' => $user?->id,
            'sender' => $sender,
            'sender_name' => $senderName,
            'recipient' => $recipient,
            'message' => $message,
            'meta' => $meta ?: null,
        ]);
    }
}
