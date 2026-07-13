<?php

namespace App\Repositories;

use App\Models\HomeSection;
use Illuminate\Database\Eloquent\Collection;

class HomeSectionRepository
{
    /**
     * @return Collection<int, HomeSection>
     */
    public function allOrdered(): Collection
    {
        return HomeSection::orderBy('display_order')->get();
    }

    /**
     * @return Collection<int, HomeSection>
     */
    public function activeOrdered(): Collection
    {
        return HomeSection::where('status', 'active')->orderBy('display_order')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): HomeSection
    {
        $data['display_order'] ??= (HomeSection::max('display_order') ?? 0) + 1;

        return HomeSection::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(HomeSection $section, array $data): HomeSection
    {
        $section->update($data);

        return $section;
    }

    public function toggleStatus(HomeSection $section): HomeSection
    {
        $section->update(['status' => $section->status === 'active' ? 'inactive' : 'active']);

        return $section;
    }

    public function moveUp(HomeSection $section): void
    {
        $neighbor = HomeSection::where('display_order', '<', $section->display_order)
            ->orderBy('display_order', 'desc')
            ->first();

        $this->swapOrder($section, $neighbor);
    }

    public function moveDown(HomeSection $section): void
    {
        $neighbor = HomeSection::where('display_order', '>', $section->display_order)
            ->orderBy('display_order')
            ->first();

        $this->swapOrder($section, $neighbor);
    }

    private function swapOrder(HomeSection $section, ?HomeSection $neighbor): void
    {
        if (! $neighbor) {
            return;
        }

        [$a, $b] = [$section->display_order, $neighbor->display_order];
        $section->update(['display_order' => $b]);
        $neighbor->update(['display_order' => $a]);
    }
}
