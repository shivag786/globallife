<?php

namespace App\Services;

use App\Models\BusinessFaq;
use App\Models\BusinessProduct;
use App\Models\BusinessSection;
use App\Models\BusinessSectionCard;
use App\Models\BusinessService;
use App\Models\VipMicrosite;
use App\Support\BusinessModules;
use App\Support\BusinessPageLayout;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The VIP page builder: one screen where a business owner reorders the
 * sections of their page, renames them, switches them on and off, and edits
 * the cards inside them — then saves it all at once.
 *
 * state() turns the microsite into the JSON the editor renders, and save()
 * applies the whole edited document back in ONE transaction, so a half-saved
 * page is impossible. A card missing from a saved section was deleted by the
 * owner (the editor asked them to confirm first). Images are uploaded the
 * moment they are cropped (see storeUpload()) and only referenced here.
 */
class PageBuilderService
{
    /** Sections whose cards are edited right on the builder. */
    private const EDITABLE = ['about', 'stats', 'services', 'products', 'faqs'];

    /** Sections shown on the builder with a link to their own page. */
    private const LINKED = [
        'shop' => 'vip.marketplace.index',
        'gallery' => 'vip.gallery.index',
        'videos' => 'vip.videos.index',
        'reviews' => 'vip.reviews.index',
        'contact' => 'vip.profile.edit',
        'social_media' => 'vip.profile.edit',
    ];

    /** Session key listing the images this owner uploaded through the builder. */
    private const UPLOADS_KEY = 'page_builder_uploads';

    /** @var array<int, string> */
    private array $pendingDeletes = [];

    /**
     * @return array<string, mixed>
     */
    public function state(VipMicrosite $microsite): array
    {
        $microsite->load([
            'services', 'products', 'faqs', 'customSections.cards', 'vipPlan',
            'galleryItems', 'videos', 'reviews', 'banners',
        ]);

        $layout = $microsite->layout();

        $sections = [];
        foreach ($layout->order($microsite->customSections) as $key) {
            $section = $this->sectionState($microsite, $layout, $key);
            if ($section !== null) {
                $sections[] = $section;
            }
        }

        return [
            'sections' => $sections,
            'quota' => [
                'services' => $microsite->contentQuota('services'),
                'products' => $microsite->contentQuota('products'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sectionState(VipMicrosite $microsite, BusinessPageLayout $layout, string $key): ?array
    {
        if (str_starts_with($key, 'custom-')) {
            $section = $microsite->customSections->firstWhere('id', (int) substr($key, 7));

            return $section ? $this->customSectionState($section) : null;
        }

        $default = BusinessPageLayout::DEFAULTS[$key];
        $isLinked = array_key_exists($key, self::LINKED);

        $state = [
            'key' => $key,
            'type' => $isLinked ? 'linked' : $key,
            'label' => $default['label'],
            'hint' => $default['hint'],
            'visible' => $microsite->isModuleVisible($key),
            'heading' => $layout->heading($key),
        ];

        if ($isLinked) {
            $state['manage_url'] = route(self::LINKED[$key]);
            $state['summary'] = $this->linkedSummary($microsite, $key);

            return $state;
        }

        return $state + match ($key) {
            'about' => ['content' => [
                'short_description' => (string) $microsite->short_description,
                'description' => (string) $microsite->description,
            ]],
            'stats' => ['items' => $layout->stats()],
            'services' => ['items' => $microsite->services->map(fn (BusinessService $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'category' => (string) $s->category,
                'short_description' => (string) $s->short_description,
                'long_description' => (string) $s->long_description,
                'image_path' => $s->image_path,
                'image_url' => $s->image_path ? asset('storage/'.$s->image_path) : null,
                'mrp' => $s->mrp,
                'offer_price' => $s->offer_price,
                'show_book_now' => (bool) $s->show_book_now,
                'is_active' => $s->status === 'published',
            ])->all()],
            'products' => ['items' => $microsite->products->map(fn (BusinessProduct $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => (string) $p->category,
                'short_description' => (string) $p->short_description,
                'long_description' => (string) $p->long_description,
                'image_path' => $p->image_path,
                'image_url' => $p->image_path ? asset('storage/'.$p->image_path) : null,
                'mrp' => $p->mrp,
                'offer_price' => $p->offer_price,
                'stock' => $p->stock,
                'is_active' => $p->status === 'published',
            ])->all()],
            'faqs' => ['items' => $microsite->faqs->map(fn (BusinessFaq $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'answer' => $f->answer,
                'is_active' => (bool) $f->is_visible,
            ])->all()],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function customSectionState(BusinessSection $section): array
    {
        return [
            'key' => $section->layoutKey(),
            'type' => 'custom',
            'id' => $section->id,
            'label' => $section->title,
            'hint' => 'Your own section. Add a card for each doctor, facility, team member or offer.',
            'visible' => (bool) $section->is_active,
            'style' => $section->style,
            'heading' => [
                'eyebrow' => (string) $section->eyebrow,
                'title' => $section->title,
                'subtitle' => (string) $section->subtitle,
            ],
            'items' => $section->cards->map(fn (BusinessSectionCard $c) => [
                'id' => $c->id,
                'title' => $c->title,
                'subtitle' => (string) $c->subtitle,
                'short_description' => (string) $c->short_description,
                'long_description' => (string) $c->long_description,
                'image_path' => $c->image_path,
                'image_url' => $c->image_path ? asset('storage/'.$c->image_path) : null,
                'button_label' => (string) $c->button_label,
                'button_url' => (string) $c->button_url,
                'is_active' => (bool) $c->is_active,
            ])->all(),
        ];
    }

    private function linkedSummary(VipMicrosite $microsite, string $key): string
    {
        $count = fn (int $n, string $one, string $many) => $n.' '.($n === 1 ? $one : $many);

        return match ($key) {
            'shop' => $count($microsite->visibleCatalogProducts()->count(), 'company product', 'company products').' on sale',
            'gallery' => $count($microsite->galleryItems->count(), 'photo', 'photos'),
            'videos' => $count($microsite->videos->count(), 'video', 'videos'),
            'reviews' => $count($microsite->reviews->where('status', 'approved')->count(), 'approved review', 'approved reviews'),
            'contact' => $microsite->phone_number ?: 'Add your phone, address and hours in Profile',
            'social_media' => 'Links from your Profile',
            default => '',
        };
    }

    /**
     * Apply an edited page. `$sections` is the editor's document in page order
     * and has already passed UpdatePageBuilderRequest.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    public function save(VipMicrosite $microsite, array $sections): void
    {
        $this->assertQuota($microsite, $sections, 'services');
        $this->assertQuota($microsite, $sections, 'products');

        $allowedImages = $this->allowedImagePaths($microsite);

        DB::transaction(function () use ($microsite, $sections, $allowedImages) {
            $layout = $microsite->layout();
            $visibility = $microsite->module_visibility ?? BusinessModules::defaults();
            $headings = [];
            $order = [];
            $stats = $layout->stats();
            $keptCustomIds = [];

            foreach (array_values($sections) as $position => $section) {
                $key = (string) $section['key'];
                $type = (string) $section['type'];

                if ($type === 'custom') {
                    $custom = $this->saveCustomSection($microsite, $section, $position, $allowedImages);
                    $keptCustomIds[] = $custom->id;
                    $order[] = $custom->layoutKey();

                    continue;
                }

                if (! array_key_exists($key, BusinessPageLayout::DEFAULTS)) {
                    continue;
                }

                $order[] = $key;
                $visibility[$key] = (bool) ($section['visible'] ?? true);
                $headings[$key] = $this->headingOverrides($layout, $key, (array) ($section['heading'] ?? []));

                // A section sent without its content (a linked section, or an
                // older editor) leaves that content exactly as it was.
                if ($key === 'about' && isset($section['content'])) {
                    $microsite->fill([
                        'short_description' => $this->text($section['content']['short_description'] ?? null),
                        'description' => $this->text($section['content']['description'] ?? null),
                    ]);
                }

                if (! in_array($key, self::EDITABLE, true) || ! array_key_exists('items', $section)) {
                    continue;
                }

                $items = (array) $section['items'];

                switch ($key) {
                    case 'stats':
                        $stats = $this->cleanStats($items);
                        break;
                    case 'services':
                        $this->syncServices($microsite, $items, $allowedImages);
                        break;
                    case 'products':
                        $this->syncProducts($microsite, $items, $allowedImages);
                        break;
                    case 'faqs':
                        $this->syncFaqs($microsite, $items);
                        break;
                }
            }

            // Custom sections the owner deleted on the builder.
            $microsite->customSections()->whereNotIn('id', $keptCustomIds)->get()
                ->each(fn (BusinessSection $section) => $this->deleteSection($section));

            $microsite->module_visibility = $visibility;
            $microsite->page_layout = [
                'order' => $order,
                'headings' => array_filter($headings),
                'stats' => $stats,
            ];
            $microsite->save();
        });

        // Only once the rows no longer point at them; a failed save keeps them.
        foreach ($this->pendingDeletes as $path) {
            Storage::disk('public')->delete($path);
        }
        $this->pendingDeletes = [];

        session()->forget(self::UPLOADS_KEY);
    }

    /**
     * Store only what the owner actually changed, so an untouched heading keeps
     * following the default (and the business name inside it).
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, string>
     */
    private function headingOverrides(BusinessPageLayout $layout, string $key, array $submitted): array
    {
        $effective = $layout->heading($key);
        $defaults = $layout->defaultHeading($key);

        $out = [];
        foreach (['eyebrow', 'title', 'subtitle'] as $field) {
            $value = trim((string) ($submitted[$field] ?? $effective[$field]));

            if ($value !== $defaults[$field]) {
                $out[$field] = $value;
            }
        }

        return $out;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{label: string, value: string, suffix: string, is_active: bool}>
     */
    private function cleanStats(array $items): array
    {
        return array_slice(array_values(array_map(fn ($stat) => [
            'label' => trim((string) ($stat['label'] ?? '')),
            'value' => trim((string) ($stat['value'] ?? '')),
            'suffix' => trim((string) ($stat['suffix'] ?? '')),
            'is_active' => (bool) ($stat['is_active'] ?? true),
        ], $items)), 0, BusinessPageLayout::MAX_STATS);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $allowedImages
     */
    private function syncServices(VipMicrosite $microsite, array $items, array $allowedImages): void
    {
        $existing = $microsite->services()->get()->keyBy('id');
        $slugs = [];
        $kept = [];

        foreach (array_values($items) as $index => $item) {
            $service = isset($item['id']) ? $existing->get((int) $item['id']) : null;
            $service ??= new BusinessService(['vip_microsite_id' => $microsite->id]);

            [$mrp, $offer] = $this->prices($item);
            $short = $this->text($item['short_description'] ?? null);

            $service->fill([
                'name' => trim((string) $item['name']),
                'slug' => $this->uniqueSlug((string) $item['name'], $slugs, $existing, $service->id),
                'category' => $this->text($item['category'] ?? null),
                'short_description' => $short,
                'long_description' => $this->text($item['long_description'] ?? null),
                'image_path' => $this->imagePath($item, $service->image_path, $allowedImages),
                'mrp' => $mrp,
                'offer_price' => $offer,
                'show_pricing' => $mrp !== null || $offer !== null,
                'show_book_now' => (bool) ($item['show_book_now'] ?? true),
                'status' => ($item['is_active'] ?? true) ? 'published' : 'draft',
                'sort_order' => $index,
                'meta_title' => trim((string) $item['name']),
                'meta_description' => $short,
                'meta_keywords' => $this->text($item['category'] ?? null),
            ]);
            $service->discount_percent = $service->discountPercentage();
            $service->strike_price = null;
            $service->save();

            $kept[] = $service->id;
        }

        $this->deleteMissing($microsite->services()->getQuery(), $kept);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, string>  $allowedImages
     */
    private function syncProducts(VipMicrosite $microsite, array $items, array $allowedImages): void
    {
        $existing = $microsite->products()->get()->keyBy('id');
        $slugs = [];
        $kept = [];

        foreach (array_values($items) as $index => $item) {
            $product = isset($item['id']) ? $existing->get((int) $item['id']) : null;
            $product ??= new BusinessProduct(['vip_microsite_id' => $microsite->id]);

            [$mrp, $offer] = $this->prices($item);
            $short = $this->text($item['short_description'] ?? null);
            $stock = $item['stock'] ?? null;

            $product->fill([
                'name' => trim((string) $item['name']),
                'slug' => $this->uniqueSlug((string) $item['name'], $slugs, $existing, $product->id),
                'category' => $this->text($item['category'] ?? null),
                'short_description' => $short,
                'long_description' => $this->text($item['long_description'] ?? null),
                'image_path' => $this->imagePath($item, $product->image_path, $allowedImages),
                'mrp' => $mrp,
                'offer_price' => $offer,
                'show_pricing' => $mrp !== null || $offer !== null,
                'stock' => $stock === null || $stock === '' ? null : max(0, (int) $stock),
                'status' => ($item['is_active'] ?? true) ? 'published' : 'draft',
                'sort_order' => $index,
                'meta_title' => trim((string) $item['name']),
                'meta_description' => $short,
                'meta_keywords' => $this->text($item['category'] ?? null),
            ]);
            $product->discount_percent = $product->discountPercentage();
            $product->strike_price = null;
            $product->save();

            $kept[] = $product->id;
        }

        $this->deleteMissing($microsite->products()->getQuery(), $kept);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function syncFaqs(VipMicrosite $microsite, array $items): void
    {
        $existing = $microsite->faqs()->get()->keyBy('id');
        $kept = [];

        foreach (array_values($items) as $index => $item) {
            $faq = isset($item['id']) ? $existing->get((int) $item['id']) : null;
            $faq ??= new BusinessFaq(['vip_microsite_id' => $microsite->id]);

            $faq->fill([
                'question' => trim((string) $item['question']),
                'answer' => trim((string) $item['answer']),
                'is_visible' => (bool) ($item['is_active'] ?? true),
                'sort_order' => $index,
            ])->save();

            $kept[] = $faq->id;
        }

        $microsite->faqs()->whereNotIn('id', $kept)->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $allowedImages
     */
    private function saveCustomSection(VipMicrosite $microsite, array $data, int $position, array $allowedImages): BusinessSection
    {
        $section = isset($data['id'])
            ? $microsite->customSections()->whereKey((int) $data['id'])->first()
            : null;
        $section ??= new BusinessSection(['vip_microsite_id' => $microsite->id]);

        $heading = (array) ($data['heading'] ?? []);

        $section->fill([
            'eyebrow' => $this->text($heading['eyebrow'] ?? null),
            'title' => trim((string) ($heading['title'] ?? '')) ?: 'Untitled section',
            'subtitle' => $this->text($heading['subtitle'] ?? null),
            'style' => in_array($data['style'] ?? null, BusinessSection::STYLES, true) ? $data['style'] : 'cards',
            'is_active' => (bool) ($data['visible'] ?? true),
            'sort_order' => $position,
        ])->save();

        $existing = $section->cards()->get()->keyBy('id');
        $kept = [];

        foreach (array_values((array) ($data['items'] ?? [])) as $index => $item) {
            $card = isset($item['id']) ? $existing->get((int) $item['id']) : null;
            $card ??= new BusinessSectionCard(['business_section_id' => $section->id]);

            $card->fill([
                'title' => trim((string) $item['title']),
                'subtitle' => $this->text($item['subtitle'] ?? null),
                'short_description' => $this->text($item['short_description'] ?? null),
                'long_description' => $this->text($item['long_description'] ?? null),
                'image_path' => $this->imagePath($item, $card->image_path, $allowedImages),
                'button_label' => $this->text($item['button_label'] ?? null),
                'button_url' => $this->text($item['button_url'] ?? null),
                'is_active' => (bool) ($item['is_active'] ?? true),
                'sort_order' => $index,
            ])->save();

            $kept[] = $card->id;
        }

        $this->deleteMissing($section->cards()->getQuery(), $kept);

        return $section;
    }

    private function deleteSection(BusinessSection $section): void
    {
        $this->deleteMissing($section->cards()->getQuery(), []);
        $section->delete();
    }

    /**
     * Delete the rows the owner removed, and their image files with them.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @param  array<int, int>  $keepIds
     */
    private function deleteMissing($query, array $keepIds): void
    {
        $query->whereNotIn('id', $keepIds)->get()->each(function ($row) {
            $this->deleteFile($row->image_path);
            $row->delete();
        });
    }

    /**
     * The package caps how many services / products a member may have. Like the
     * standalone forms, the cap only blocks ADDING: a member already over it
     * after a downgrade keeps every existing row and can still edit them.
     *
     * @param  array<int, array<string, mixed>>  $sections
     */
    private function assertQuota(VipMicrosite $microsite, array $sections, string $type): void
    {
        $section = collect($sections)->firstWhere('key', $type);

        if (! $section || ! array_key_exists('items', $section)) {
            return;
        }

        $items = (array) $section['items'];

        // "New" means not one of this member's existing rows, not merely "sent
        // without an id": an id that is not theirs is created as a new row by the
        // sync, so it must count against the cap too.
        $ownIds = ($type === 'services' ? $microsite->services() : $microsite->products())->pluck('id')->all();
        $added = count(array_filter($items, fn ($item) => ! in_array((int) ($item['id'] ?? 0), $ownIds, true)));

        if ($added === 0) {
            return;
        }

        $quota = $microsite->contentQuota($type);

        if (count($items) > $quota['limit']) {
            $label = $type === 'services' ? 'service' : 'product';

            throw ValidationException::withMessages([
                $type => sprintf(
                    'Your plan allows %d %s%s. Remove one before adding another, or ask your Commission Partner to move you to a bigger package.',
                    $quota['limit'],
                    $label,
                    $quota['limit'] === 1 ? '' : 's',
                ),
            ]);
        }
    }

    /**
     * MRP and sale price, the only two figures entered. A sale price above the
     * MRP has already been refused by the request.
     *
     * @param  array<string, mixed>  $item
     * @return array{0: ?float, 1: ?float}
     */
    private function prices(array $item): array
    {
        $num = fn ($v) => $v === null || $v === '' ? null : round((float) $v, 2);

        return [$num($item['mrp'] ?? null), $num($item['offer_price'] ?? null)];
    }

    /**
     * Keep the current image, take a freshly uploaded one, or clear it. A path
     * the owner did not upload is ignored, so a crafted request cannot point a
     * card at someone else's file.
     *
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $allowed
     */
    private function imagePath(array $item, ?string $current, array $allowed): ?string
    {
        $path = $item['image_path'] ?? null;

        if ($path === null || $path === '') {
            if ($current !== null && array_key_exists('image_path', $item)) {
                $this->deleteFile($current);
            }

            return array_key_exists('image_path', $item) ? null : $current;
        }

        if ($path === $current) {
            return $current;
        }

        if (! in_array($path, $allowed, true)) {
            return $current;
        }

        $this->deleteFile($current);

        return $path;
    }

    /**
     * @return array<int, string>
     */
    private function allowedImagePaths(VipMicrosite $microsite): array
    {
        return array_values(array_filter(
            (array) session(self::UPLOADS_KEY, []),
            fn ($path) => is_string($path) && str_starts_with($path, 'uploads/builder/'.$microsite->id.'/'),
        ));
    }

    /**
     * Store a cropped image and remember that this session uploaded it.
     */
    public function storeUpload(VipMicrosite $microsite, UploadedFile $file): string
    {
        $path = $file->store('uploads/builder/'.$microsite->id, 'public');

        session()->push(self::UPLOADS_KEY, $path);

        return $path;
    }

    /**
     * Queue a replaced or removed image for deletion after the save commits.
     * Only files the builder itself wrote: seeded and legacy images may be
     * shared between rows.
     */
    private function deleteFile(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/builder/')) {
            $this->pendingDeletes[] = $path;
        }
    }

    /**
     * A URL-safe slug unique within this microsite, tracking the ones handed
     * out during this save so two new cards with the same name do not collide.
     *
     * @param  array<int, string>  $taken
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $existing
     */
    private function uniqueSlug(string $name, array &$taken, $existing, ?int $selfId): string
    {
        $base = Str::slug($name) ?: 'item';
        $others = $existing->reject(fn ($row) => $row->id === $selfId)->pluck('slug')->all();

        $slug = $base;
        $n = 2;
        while (in_array($slug, $taken, true) || in_array($slug, $others, true)) {
            $slug = $base.'-'.$n++;
        }

        $taken[] = $slug;

        return $slug;
    }

    private function text(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
