<?php

namespace App\Modules\Membership\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMembershipTypeRequest extends StoreMembershipTypeRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['name'] = ['required', 'string', 'max:50',
            Rule::unique('membership_types', 'name')->ignore($this->route('membershipType'))];

        return $rules;
    }
}
