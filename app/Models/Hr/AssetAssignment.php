<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['asset_id', 'employee_id', 'assigned_on', 'returned_on', 'return_note'])]
class AssetAssignment extends Model
{
    protected function casts(): array
    {
        return ['assigned_on' => 'date', 'returned_on' => 'date'];
    }

    /** @return BelongsTo<Asset, $this> */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
