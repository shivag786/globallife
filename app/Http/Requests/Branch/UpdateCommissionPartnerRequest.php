<?php

namespace App\Http\Requests\Branch;

use App\Rules\CityWithinBranchManager;
use App\Rules\PercentageWithinCap;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommissionPartnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('branch_manager')
            && $this->route('commissionPartner')->created_by === $this->user()->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedCityIds = $this->user()->branchCities()->pluck('cities.id')->all();

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('commissionPartner')->id],
            'cities' => ['required', 'array', 'min:1'],
            'cities.*' => ['integer', 'exists:cities,id', new CityWithinBranchManager($allowedCityIds)],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap((float) ($this->user()->commission_percentage ?? 0))],
        ];
    }
}
