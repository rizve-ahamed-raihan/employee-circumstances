<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class OrganizationNotificationService
{
    public function sendToOrganization(Organization $organization, string $title, string $message): void
    {
        $users = $organization->users ?? collect();
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $message);
        }
        Log::info("Organization notification sent to {$organization->id}: {$title}");
    }

    public function sendToMany(Collection $users, string $title, string $message): void
    {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $message);
        }
        Log::info("Notification sent to " . $users->count() . " users: {$title}");
    }

    protected function sendToUser(User $user, string $title, string $message): void
    {
        Log::info("Notification to user {$user->id} ({$user->email}): {$title} - {$message}");
    }
}
