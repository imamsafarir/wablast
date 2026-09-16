<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'role', 'password', 'wa_instance_name'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Mendapatkan nama WhatsApp instance yang unik milik pengguna.
     */
    public function getWaInstanceName(): string
    {
        if (! empty($this->wa_instance_name)) {
            return $this->wa_instance_name;
        }

        // Buat nama instance default berbasis nama pengguna
        $baseName = 'user_'.preg_replace('/[^a-zA-Z0-9_]/', '', strtolower((string) ($this->username ?: $this->name)));
        if (empty($baseName) || $baseName === 'user_') {
            $baseName = 'user_'.$this->id;
        }

        $candidate = $baseName;
        $counter = 1;
        while (static::where('wa_instance_name', $candidate)->where('id', '!=', $this->id)->exists()) {
            $candidate = "{$baseName}_{$counter}";
            $counter++;
        }

        $this->update(['wa_instance_name' => $candidate]);

        return $candidate;
    }

    /**
     * Memeriksa apakah nama instance sudah digunakan oleh pengguna lain.
     */
    public function isInstanceTakenByOther(string $instanceName): bool
    {
        return static::where('wa_instance_name', $instanceName)
            ->where('id', '!=', $this->id)
            ->exists();
    }

    /**
     * Check if user is superadmin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user has administrative access (superadmin or admin).
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    /**
     * Check if user is standard pengguna.
     */
    public function isPengguna(): bool
    {
        return $this->role === 'pengguna';
    }
}
