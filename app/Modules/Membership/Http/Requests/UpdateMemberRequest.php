<?php

namespace App\Modules\Membership\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateMemberRequest extends StoreMemberRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['member_code'] = ['required', 'string', 'max:30',
            Rule::unique('members', 'member_code')->ignore($this->route('member'))];

        return $rules;
    }
}
