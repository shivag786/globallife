<?php

namespace App\Repositories;

use App\Models\VipPlan;
use Illuminate\Database\Eloquent\Collection;

class VipPlanRepository
{
    /**
     * @return Collection<int, VipPlan>
     */
    public function allOrdered(): Collection
    {
        return VipPlan::orderBy('display_order')->get();
    }

    /**
     * @return Collection<int, VipPlan>
     */
    public function activeOrdered(): Collection
    {
        return VipPlan::where('status', 'active')->orderBy('display_order')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VipPlan
    {
        return VipPlan::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VipPlan $plan, array $data): VipPlan
    {
        $plan->update($data);

        return $plan;
    }
}
