<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\VipPlan;
use App\Repositories\LeadRepository;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Lead::count() > 0) {
            return;
        }

        $goldPlan = VipPlan::where('slug', 'gold-vip')->first();
        $repository = app(LeadRepository::class);

        $leads = [
            ['name' => 'Ramesh Gupta', 'email' => 'ramesh.gupta@example.com', 'phone' => '+91 98765 43210', 'city' => 'Jhansi', 'message' => 'Interested in becoming a distributor.', 'source' => 'contact_page'],
            ['name' => 'Sneha Kapoor', 'email' => 'sneha.kapoor@example.com', 'phone' => '+91 91234 56780', 'city' => 'Delhi', 'message' => 'Would like more details on the Gold VIP plan.', 'source' => 'homepage', 'interested_plan_id' => $goldPlan?->id],
            ['name' => 'Arjun Mehta', 'email' => 'arjun.mehta@example.com', 'city' => 'Mumbai', 'message' => 'General product enquiry.', 'source' => 'chatbot'],
        ];

        foreach ($leads as $lead) {
            $repository->create($lead);
        }
    }
}
