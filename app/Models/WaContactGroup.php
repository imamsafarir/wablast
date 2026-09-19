<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaContactGroup extends Model
{
    protected $guarded = [];

    protected $appends = ['count'];

    public function getCountAttribute(): int
    {
        return count(array_filter(explode("\n", str_replace("\r", '', $this->nomor ?? ''))));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
