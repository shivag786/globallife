<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['name', 'asset_tag', 'category', 'serial_number', 'purchase_date', 'purchase_cost', 'status', 'employee_id', 'assigned_on', 'notes'])]
class Asset extends Model
{
    use LogsActivity;

    public const CATEGORIES = ['Laptop', 'Desktop', 'Mobile', 'SIM Card', 'ID Card', 'Vehicle', 'Furniture', 'Other'];

    public const STATUSES = ['available' => 'Available', 'assigned' => 'Assigned', 'repair' => 'In Repair', 'retired' => 'Retired'];

    protected function casts(): array
    {
        return ['purchase_date' => 'date', 'assigned_on' => 'date', 'purchase_cost' => 'float'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'employee_id'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return HasMany<AssetAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class)->latest('assigned_on');
    }
}
