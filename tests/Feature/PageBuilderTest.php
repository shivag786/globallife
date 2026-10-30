<?php

namespace Tests\Feature;

use App\Models\BusinessFaq;
use App\Models\BusinessSection;
use App\Models\BusinessService;
use App\Models\User;
use App\Models\VipMicrosite;
use App\Models\VipPlan;
use App\Services\PageBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsCommissionChain;
use Tests\TestCase;

/**
 * The VIP page builder: one document saved at once — order, headings,
 * show/hide, highlights, and the cards inside each section.
 */
class PageBuilderTest extends TestCase
{
    use BuildsCommissionChain, RefreshDatabase;

    private VipPlan $plan;

    private User $owner;

    private VipMicrosite $microsite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();

        $partner = $this->makeCommissionPartner($this->makeBranchManager(30), 20);
        $this->plan = $this->makePlan(4999);
        $this->plan->update(['product_limit' => 3, 'service_limit' => 2]);

        [$this->owner, $this->microsite] = $this->makeVipMember($partner, $this->plan, $this->makeCity());
        $this->microsite->update([
            'activated_at' => now(), 'plan_expires_at' => now()->addYear(),
            'description' => 'We heal.', 'phone_number' => '9876543210',
        ]);
    }

    /**
     * The document exactly as the editor would send it back unchanged.
     *
     * @return array<int, array<string, mixed>>
     */
    private function document(): array
    {
        $this->actingAs($this->owner);
        $state = app(PageBuilderService::class)->state($this->microsite->fresh());

        return array_map(function (array $section) {
            $out = ['key' => $section['key'], 'type' => $section['type'], 'visible' => $section['visible'], 'heading' => $section['heading']];
            if ($section['type'] === 'custom') {
                $out += ['id' => $section['id'], 'style' => $section['style']];
            }
            if ($section['type'] === 'about') {
                $out['content'] = $section['content'];
            }
            if (array_key_exists('items', $section)) {
                $out['items'] = array_map(function ($item) {
                    unset($item['image_url']);

                    return $item;
                }, $section['items']);
            }

            return $out;
        }, $state['sections']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $doc
     */
    private function save(array $doc): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->owner)->putJson(route('vip.page-builder.update'), ['sections' => $doc]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $doc
     */
    private function indexOf(array $doc, string $key): int
    {
        foreach ($doc as $i => $section) {
            if ($section['key'] === $key) {
                return $i;
            }
        }

        $this->fail("No section $key");
    }

    public function test_the_builder_renders_for_the_owner_only(): void
    {
        $this->actingAs($this->owner)->get(route('vip.page-builder.edit'))
            ->assertOk()
            ->assertSee('data-page-builder', false)
            ->assertSee('page-builder-state', false)
            ->assertSee('Save changes', false);

        $this->actingAs($this->makeCommissionPartner($this->makeBranchManager(10), 5))
            ->get(route('vip.page-builder.edit'))->assertForbidden();
    }

    public function test_saving_unchanged_keeps_the_page_exactly_as_it_was(): void
    {
        $before = $this->get($this->microsite->publicPath())->assertOk()->getContent();

        $this->save($this->document())->assertOk();

        $this->assertSame([], $this->microsite->fresh()->page_layout['headings']);
        $after = $this->get($this->microsite->publicPath())->assertOk();
        $after->assertSee('About '.$this->microsite->business_name);
        $this->assertStringContainsString('We heal.', $before);
    }

    public function test_reorder_rename_hide_and_highlights_show_on_the_public_page(): void
    {
        $doc = $this->document();

        // Contact to the top, rename About, hide Reviews, add two highlights.
        $contact = array_splice($doc, $this->indexOf($doc, 'contact'), 1)[0];
        array_unshift($doc, $contact);
        $doc[$this->indexOf($doc, 'about')]['heading']['title'] = 'Our Story';
        $doc[$this->indexOf($doc, 'reviews')]['visible'] = false;
        $doc[$this->indexOf($doc, 'stats')]['items'] = [
            ['label' => 'Beds', 'value' => '250', 'suffix' => '+', 'is_active' => true],
            ['label' => 'Hidden one', 'value' => '9', 'suffix' => '', 'is_active' => false],
        ];

        $this->save($doc)->assertOk()->assertJsonPath('message', 'Your page has been saved.');

        $microsite = $this->microsite->fresh();
        $this->assertSame('contact', $microsite->page_layout['order'][0]);
        $this->assertSame(['title' => 'Our Story'], $microsite->page_layout['headings']['about']);
        $this->assertFalse($microsite->isModuleVisible('reviews'));

        $html = $this->get($microsite->publicPath())->assertOk()
            ->assertSee('Our Story')
            ->assertSee('data-countup="250+"', false)
            ->assertDontSee('Hidden one')
            ->assertDontSee('What Customers Say')
            ->getContent();

        $this->assertLessThan(strpos($html, 'id="about"'), strpos($html, 'id="contact"'));
    }

    public function test_cards_are_added_updated_reordered_and_deleted_in_one_save(): void
    {
        $keep = BusinessService::create(['vip_microsite_id' => $this->microsite->id, 'name' => 'Keep', 'slug' => 'keep', 'status' => 'published', 'sort_order' => 0]);
        $drop = BusinessService::create(['vip_microsite_id' => $this->microsite->id, 'name' => 'Drop', 'slug' => 'drop', 'status' => 'published', 'sort_order' => 1]);

        $doc = $this->document();
        $s = $this->indexOf($doc, 'services');
        $doc[$s]['items'] = [
            ['id' => null, 'name' => 'New Scan', 'short_description' => 'Fast', 'mrp' => 1000, 'offer_price' => 800, 'is_active' => false],
            ['id' => $keep->id, 'name' => 'Keep Renamed', 'is_active' => true],
        ];

        $this->save($doc)->assertOk();

        $this->assertNull(BusinessService::find($drop->id));
        $this->assertSame('Keep Renamed', $keep->fresh()->name);
        $this->assertSame(1, $keep->fresh()->sort_order);

        $new = BusinessService::where('name', 'New Scan')->sole();
        $this->assertSame(0, $new->sort_order);
        $this->assertSame('draft', $new->status);
        $this->assertSame('new-scan', $new->slug);
        $this->assertSame('20.00', (string) $new->discount_percent);

        // Hidden cards are kept but not shown.
        $this->get($this->microsite->publicPath())->assertSee('Keep Renamed')->assertDontSee('New Scan');
    }

    public function test_a_new_custom_section_with_people_cards_is_created_then_deleted(): void
    {
        $doc = $this->document();
        array_splice($doc, $this->indexOf($doc, 'contact'), 0, [[
            'key' => 'new-abc123', 'type' => 'custom', 'id' => null, 'style' => 'people', 'visible' => true,
            'heading' => ['eyebrow' => 'Specialists', 'title' => 'Meet Our Doctors', 'subtitle' => ''],
            'items' => [
                ['title' => 'Dr. Anjali Mehra', 'subtitle' => 'DM Cardiology', 'short_description' => 'Heart', 'long_description' => 'Long bio', 'button_label' => 'Call', 'button_url' => 'tel:9876543210', 'is_active' => true],
            ],
        ]]);

        $response = $this->save($doc)->assertOk();

        $section = BusinessSection::sole();
        $this->assertSame('people', $section->style);
        $this->assertSame('Dr. Anjali Mehra', $section->cards()->sole()->title);
        $this->assertContains('custom-'.$section->id, $this->microsite->fresh()->page_layout['order']);
        $this->assertContains('custom-'.$section->id, array_column($response->json('state.sections'), 'key'));

        $this->get($this->microsite->publicPath())->assertSee('Meet Our Doctors')->assertSee('Dr. Anjali Mehra')->assertSee('Long bio');

        // Leaving it out of the next save deletes it and its cards.
        $doc = array_values(array_filter($this->document(), fn ($s) => $s['type'] !== 'custom'));
        $this->save($doc)->assertOk();
        $this->assertSame(0, BusinessSection::count());
    }

    public function test_the_package_cap_blocks_adding_but_not_editing(): void
    {
        $doc = $this->document();
        $s = $this->indexOf($doc, 'services');
        $doc[$s]['items'] = [
            ['name' => 'One', 'is_active' => true],
            ['name' => 'Two', 'is_active' => true],
            ['name' => 'Three', 'is_active' => true],
        ];

        $this->save($doc)->assertStatus(422)->assertJsonValidationErrors('services');
        $this->assertSame(0, BusinessService::count());

        // Made-up ids are still new rows, so they count against the cap.
        $doc[$s]['items'] = array_map(fn ($n) => ['id' => 900000 + $n, 'name' => "Forged $n", 'is_active' => true], range(1, 3));
        $this->save($doc)->assertStatus(422)->assertJsonValidationErrors('services');
        $this->assertSame(0, BusinessService::count());

        $doc[$s]['items'] = [['name' => 'One', 'is_active' => true], ['name' => 'Two', 'is_active' => true]];
        $this->save($doc)->assertOk();
        $this->assertSame(2, BusinessService::count());
    }

    public function test_validation_points_at_the_field_that_needs_fixing(): void
    {
        $doc = $this->document();
        $s = $this->indexOf($doc, 'services');
        $doc[$s]['items'] = [['name' => '', 'mrp' => 100, 'offer_price' => 200, 'is_active' => true]];
        $f = $this->indexOf($doc, 'faqs');
        $doc[$f]['items'] = [['question' => 'Open on Sunday?', 'answer' => '', 'is_active' => true]];

        $this->save($doc)->assertStatus(422)->assertJsonValidationErrors([
            "sections.$s.items.0.name",
            "sections.$s.items.0.offer_price",
            "sections.$f.items.0.answer",
        ]);
    }

    public function test_cards_can_only_use_images_this_owner_uploaded(): void
    {
        Storage::fake('public');

        $upload = $this->actingAs($this->owner)
            ->post(route('vip.page-builder.upload'), ['image' => UploadedFile::fake()->image('crop.webp', 800, 600)], ['Accept' => 'application/json'])
            ->assertOk()
            ->json();

        Storage::disk('public')->assertExists($upload['path']);

        $doc = $this->document();
        $p = $this->indexOf($doc, 'products');
        $doc[$p]['items'] = [
            ['name' => 'Mine', 'image_path' => $upload['path'], 'is_active' => true],
            ['name' => 'Not mine', 'image_path' => 'uploads/someone-elses.png', 'is_active' => true],
        ];

        $this->save($doc)->assertOk();

        $this->assertSame($upload['path'], $this->microsite->products()->where('name', 'Mine')->value('image_path'));
        $this->assertNull($this->microsite->products()->where('name', 'Not mine')->value('image_path'));
    }

    public function test_ids_from_another_business_cannot_be_edited_through_the_builder(): void
    {
        [, $other] = $this->makeVipMember($this->makeCommissionPartner($this->makeBranchManager(30), 20), $this->plan, $this->makeCity());
        $theirs = BusinessFaq::create(['vip_microsite_id' => $other->id, 'question' => 'Theirs?', 'answer' => 'Yes', 'is_visible' => true]);

        $doc = $this->document();
        $doc[$this->indexOf($doc, 'faqs')]['items'] = [
            ['id' => $theirs->id, 'question' => 'Hijacked?', 'answer' => 'No', 'is_active' => true],
        ];

        $this->save($doc)->assertOk();

        $this->assertSame('Theirs?', $theirs->fresh()->question);
        $this->assertSame('Hijacked?', $this->microsite->faqs()->sole()->question);
    }

    public function test_only_vip_members_can_save(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $this->actingAs($admin)->putJson(route('vip.page-builder.update'), ['sections' => []])->assertForbidden();
    }
}
