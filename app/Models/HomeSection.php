<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'type', 'title', 'subtitle', 'content', 'image_path', 'cta_label', 'cta_url',
    'items', 'status', 'display_order',
])]
class HomeSection extends Model
{
    use HasFactory, LogsActivity;

    /**
     * All section types selectable in the Homepage Builder, keyed by value with an admin-facing label.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'hero' => 'Hero Banner',
        'founder_quote' => 'Founder Quote',
        'about' => 'About Us',
        'features' => 'Features / Why Choose Us',
        'products_showcase' => 'Product Showcase (live, pulls featured products)',
        'category_products' => 'Shop by Category (live, category image tiles linking to each category page)',
        'products_by_category' => 'Products by Category (live, product cards grouped by category — direct add to cart)',
        'stats' => 'Stats Counters',
        'blog_showcase' => 'Blog Showcase (live, pulls latest posts)',
        'testimonials_showcase' => 'Testimonials (live, pulls active testimonials)',
        'presence_map' => 'Pan-India Presence (live, pulls active cities)',
        'upcoming_events' => 'Upcoming Events (live, pulls upcoming events)',
        'business_opportunity' => 'Business Opportunity',
        'process_steps' => 'How It Works (process steps)',
        'vip_plans' => 'VIP Plans (live, pulls active plans)',
        'enquiry_form' => 'Enquiry Form',
        'gallery' => 'Gallery (live, pulls active media)',
        'team' => 'Team Members',
        'certifications' => 'Certifications / Trust Badges',
        'quality' => 'Quality & Transparency',
        'cta' => 'Call to Action',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
