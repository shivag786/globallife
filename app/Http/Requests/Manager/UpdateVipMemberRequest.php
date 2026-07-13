<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVipMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('commission_partner')
            && $this->route('vipMember')->created_by === $this->user()->id;
    }

    /**
     * Business name/city are not editable here — the public microsite URL must stay
     * stable once shared, so only account and plan/description details can change.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('vipMember')->id],
            'vip_plan_id' => ['required', 'integer', 'exists:vip_plans,id'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
