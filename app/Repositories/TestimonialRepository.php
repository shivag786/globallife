<?php

namespace App\Repositories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;

class TestimonialRepository
{
    /**
     * @return Collection<int, Testimonial>
     */
    public function allOrdered(): Collection
    {
        return Testimonial::orderBy('display_order')->get();
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function activeOrdered(int $limit = 3): Collection
    {
        return Testimonial::where('status', 'active')->orderBy('display_order')->limit($limit)->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Testimonial
    {
        return Testimonial::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        $testimonial->update($data);

        return $testimonial;
    }
}
