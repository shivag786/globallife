<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'name', 'slug', 'category', 'tags', 'badge', 'short_description', 'long_description',
    'main_image', 'specs', 'is_featured', 'status', 'display_order',
    'meta_title', 'meta_description', 'canonical_url',
])]
class Product extends Model
{
    use HasFactory, LogsActivity;

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'specs' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
