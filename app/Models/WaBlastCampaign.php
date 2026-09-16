<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaBlastCampaign extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'anti_bot' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WaBlastRecipient::class);
    }

    public function failedRecipients(): HasMany
    {
        return $this->hasMany(WaBlastRecipient::class)->where('status', 'failed');
    }

    public function sentRecipients(): HasMany
    {
        return $this->hasMany(WaBlastRecipient::class)->where('status', 'sent');
    }

    public function pendingRecipients(): HasMany
    {
        return $this->hasMany(WaBlastRecipient::class)->where('status', 'pending');
    }
}
