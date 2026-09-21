<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'slug', 'monthly_price', 'yearly_price', 'joining_price', 'renewal_price', 'validity_months',
    'product_limit', 'service_limit',
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

    /**
     * Length of one paid cycle. Guards against a 0/null slipping in, which would
     * make every renewal expire the instant it was approved.
     */
    public function validityMonths(): int
    {
        return max(1, (int) ($this->validity_months ?: 12));
    }

    /**
     * How many of the VIP's own products (`business_products`) this package allows.
     */
    public function productLimit(): int
    {
        return max(0, (int) $this->product_limit);
    }

    /**
     * How many services (`business_services`) this package allows.
     */
    public function serviceLimit(): int
    {
        return max(0, (int) $this->service_limit);
    }

    /**
     * Human label for the package's validity, e.g. "3 Months" or "1 Year".
     */
    public function validityLabel(): string
    {
        $months = $this->validityMonths();

        return match (true) {
            $months % 12 === 0 => ($months / 12).' Year'.($months > 12 ? 's' : ''),
            default => $months.' Month'.($months > 1 ? 's' : ''),
        };
    }

    public function isMostPopular(): bool
    {
        return in_array('Most Popular', $this->highlight_features ?? [], true);
    }
}
