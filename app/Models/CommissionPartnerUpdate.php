<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A record that Super Admin changed a Commission Partner's details, addressed
 * to that partner's Branch Manager.
 *
 * `changed_fields` maps each changed field to [old, new], e.g.
 * ['commission_percentage' => ['20.00', '25.00']].
 *
 * Deliberately not called `changes`: every Eloquent model already has an
 * internal $changes property, which $this->changes would silently read
 * instead of the column.
 */
#[Fillable([
    'commission_partner_id', 'branch_manager_id', 'updated_by', 'changed_fields', 'note', 'read_at',
])]
class CommissionPartnerUpdate extends Model
{
    /** Human labels for the fields Super Admin can change. */
    public const FIELD_LABELS = [
        'name' => 'Name',
        'email' => 'Email',
        'mobile' => 'Mobile',
        'commission_percentage' => 'Commission',
    ];

    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'commission_partner_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function branchManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_manager_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Each change as a readable line: "Commission: 20% → 25%".
     *
     * @return list<array{label: string, old: string, new: string}>
     */
    public function describedChanges(): array
    {
        $lines = [];

        foreach ($this->changed_fields ?? [] as $field => [$old, $new]) {
            $format = fn ($value) => match (true) {
                $value === null || $value === '' => '—',
                $field === 'commission_percentage' => rtrim(rtrim(number_format((float) $value, 2), '0'), '.').'%',
                default => (string) $value,
            };

            $lines[] = [
                'label' => self::FIELD_LABELS[$field] ?? ucfirst(str_replace('_', ' ', $field)),
                'old' => $format($old),
                'new' => $format($new),
            ];
        }

        return $lines;
    }
}
