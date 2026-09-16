<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        // Hindari pencatatan log login ganda dalam rentang 5 detik
        $recentLog = ActivityLog::where('user_id', $user->id)
            ->where('action', 'auth_login')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentLog) {
            return;
        }

        ActivityLog::record(
            action: 'auth_login',
            description: "Pengguna {$user->name} ({$user->email}) berhasil login ke sistem.",
            subject: $user,
            properties: [
                'guard' => $event->guard,
            ],
            userId: $user->id
        );
    }
}
