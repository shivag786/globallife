<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'name', 'phone', 'address', 'city', 'state', 'pincode', 'is_default',
])]
class Address extends Model
{
    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One-line summary for compact display.
     */
    public function summary(): string
    {
        return "{$this->address}, {$this->city}, {$this->state} - {$this->pincode}";
    }
}
