<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Str;

/**
 * The state → city directory behind the cascading city picker, plus the
 * find-or-create used when someone types a city that is not on the list.
 *
 * `cities.slug` is globally unique because it forms the first segment of a
 * microsite URL (`/{citySlug}/{businessSlug}/{secureId}`), so two cities of the
 * same name in different states cannot both be `springfield`. New slugs are
 * therefore disambiguated with the state, then with a counter.
 */
class CityDirectoryService
{
    /**
     * Every state that has at least one active city, alphabetically.
     *
     * @return list<string>
     */
    public function states(): array
    {
        return City::query()
            ->where('status', 'active')
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * State → its active cities, shaped for the picker's JSON payload.
     *
     * @return array<string, list<array{id: int, name: string}>>
     */
    public function citiesByState(): array
    {
        return City::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'state'])
            ->groupBy('state')
            ->map(fn ($cities) => $cities
                ->map(fn (City $city) => ['id' => $city->id, 'name' => $city->name])
                ->values()
                ->all())
            ->all();
    }

    /**
     * Find the city with this name in this state, or create it.
     *
     * Matching is case-insensitive on name + state so "new delhi" does not
     * create a second "New Delhi". A city that already exists is returned as-is
     * rather than duplicated, even if it is inactive.
     */
    public function resolveOrCreate(string $name, string $state): City
    {
        $name = Str::squish($name);
        $state = Str::squish($state);

        $existing = City::whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->whereRaw('LOWER(state) = ?', [Str::lower($state)])
            ->first();

        if ($existing) {
            return $existing;
        }

        return City::create([
            'name' => $name,
            'state' => $state,
            'slug' => $this->uniqueSlug($name, $state),
            'status' => 'active',
        ]);
    }

    /**
     * A free slug: the name, else name-state, else a numbered suffix.
     */
    private function uniqueSlug(string $name, string $state): string
    {
        $candidates = [Str::slug($name), Str::slug($name.'-'.$state)];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && ! City::where('slug', $candidate)->exists()) {
                return $candidate;
            }
        }

        $base = Str::slug($name.'-'.$state) ?: Str::slug($name) ?: 'city';

        for ($i = 2; $i < 500; $i++) {
            if (! City::where('slug', $base.'-'.$i)->exists()) {
                return $base.'-'.$i;
            }
        }

        return $base.'-'.Str::lower(Str::random(6));
    }
}
