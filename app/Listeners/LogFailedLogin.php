<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $credentials = $event->credentials;
        $attemptedEmail = $credentials['email'] ?? 'Unknown';

        ActivityLog::record(
            action: 'auth_failed',
            description: "Percobaan login gagal untuk akun: {$attemptedEmail}.",
            subject: $event->user,
            properties: [
                'attempted_email' => $attemptedEmail,
                'guard' => $event->guard,
            ],
            userId: $event->user?->id
        );
    }
}
