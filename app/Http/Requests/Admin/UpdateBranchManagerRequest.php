<?php

namespace App\Http\Requests\Admin;

use App\Rules\PercentageWithinCap;
use App\Support\BranchManagerAccess;
use App\Support\MobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership of this particular Branch Manager is checked in the
        // controller; this only asks whether the module is theirs at all.
        return BranchManagerAccess::canManage($this->user())
            && $this->user()->can('branch-managers.edit');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Reassigning to another Sub Admin is Super Admin's alone, so the key
            // is dropped outright for anyone else rather than validated and then
            // quietly ignored — see BranchManagerService::updateBranchManager().
            ...(BranchManagerAccess::seesAll($this->user())
                ? ['created_by' => ['nullable', 'integer', 'exists:users,id']]
                : []),
            'name' => ['required', 'string', 'max:120'],
            // Exactly 10 digits after normalisation, and no two accounts may
            // share one — `users.mobile` is written here and from the VIP
            // profile, so both have to agree or the rule means nothing.
            'mobile' => ['nullable', 'digits:'.MobileNumber::LENGTH, Rule::unique('users', 'mobile')->ignore($this->route('branchManager')->id)],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->route('branchManager')->id],
            // Optional: a Branch Manager may be created before their territory is
            // decided, and they pick up cities anyway when assigning a partner.
            'cities' => ['nullable', 'array'],
            'cities.*' => ['integer', 'exists:cities,id'],
            'commission_percentage' => ['required', 'numeric', 'min:0', new PercentageWithinCap(100)],
        ];
    }

    /**
     * Normalise the mobile before any rule sees it, so "+91 98765 43210" and
     * "09876543210" both validate and store as the same 10 digits.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('mobile')) {
            $this->merge(['mobile' => MobileNumber::normalise($this->input('mobile'))]);
        }
    }
}
