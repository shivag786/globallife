<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityRequest;
use App\Http\Requests\Admin\UpdateCityRequest;
use App\Models\City;
use App\Repositories\CityRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CityController extends Controller
{
    public function __construct(private readonly CityRepository $cities)
    {
    }

    public function index(): View
    {
        return view('admin.cities.index', ['cities' => $this->cities->allOrderedByName()]);
    }

    public function create(): View
    {
        return view('admin.cities.create');
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $this->cities->create($request->validated());

        return redirect()->route('admin.cities.index')->with('status', 'City created successfully.');
    }

    public function edit(City $city): View
    {
        return view('admin.cities.edit', ['city' => $city]);
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $this->cities->update($city, $request->validated());

        return redirect()->route('admin.cities.index')->with('status', 'City updated successfully.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return redirect()->route('admin.cities.index')->with('status', 'City deleted.');
    }
}
