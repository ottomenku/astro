<?php

namespace App\Services;

use App\Models\AstrologyEntry;
use App\Models\User;

class AstrologyEntryClickService
{
    /**
     * @return array<string, mixed>
     */
    public function initialClickAttributes(User $user): array
    {
        $now = now();

        return [
            'click_count' => 1,
            'first_clicked_by_user_id' => $user->id,
            'first_clicked_at' => $now,
            'last_clicked_at' => $now,
        ];
    }

    public function recordClick(AstrologyEntry $entry, User $user): AstrologyEntry
    {
        $updates = [
            'click_count' => $entry->click_count + 1,
            'last_clicked_at' => now(),
        ];

        if ($entry->first_clicked_by_user_id === null) {
            $updates['first_clicked_by_user_id'] = $user->id;
            $updates['first_clicked_at'] = now();
        }

        $entry->update($updates);

        return $entry->fresh(['createdBy', 'firstClickedBy']);
    }
}
