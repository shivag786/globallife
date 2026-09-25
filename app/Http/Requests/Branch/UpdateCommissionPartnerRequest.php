<?php

namespace App\Http\Requests\Branch;

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
     * Cities are not set here any more. A Commission Partner picks up a city when
     * they register a VIP Member in one, so the territory follows the work.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('commissionPartner')->id],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap((float) ($this->user()->commission_percentage ?? 0))],
        ];
    }
}
