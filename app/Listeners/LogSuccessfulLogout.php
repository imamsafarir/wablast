<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $user = $event->user;
        if (! $user) {
            return;
        }

        // Hindari pencatatan log logout ganda dalam rentang 5 detik
        $recentLog = ActivityLog::where('user_id', $user->id)
            ->where('action', 'auth_logout')
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($recentLog) {
            return;
        }

        ActivityLog::record(
            action: 'auth_logout',
            description: "Pengguna {$user->name} ({$user->email}) keluar (logout) dari sistem.",
            subject: $user,
            properties: [
                'guard' => $event->guard,
            ],
            userId: $user->id
        );
    }
}
