<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'slug', 'monthly_price', 'yearly_price', 'joining_price', 'renewal_price',
    'features', 'highlight_features', 'status', 'upgrade_priority', 'display_order',
    'microsite_limit', 'landing_page_limit', 'blog_limit', 'analytics_limit_days', 'storage_limit_mb',
])]
class VipPlan extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'highlight_features' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
