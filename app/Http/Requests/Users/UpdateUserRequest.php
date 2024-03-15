<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Update User Request
 *
 * @package \App\Http\Requests\Users
 */
class UpdateUserRequest extends CreateUserRequest
{
    /**
     * @return array
     */
    public function rules() : array
    {
        $roles = config('permission.project_roles');

        return [
            'first_name' => array_merge($this->textRules(), ['sometimes']),
            'last_name'  => array_merge($this->textRules(), ['nullable']),
            'email'      => ['sometimes', 'email', Rule::unique(User::class)->ignore($this->route('user'))],
            'password'   => array_merge(['sometimes'], $this->passwordRules()),
            'role'       => ['sometimes', $this->roleExistRule($roles)],
        ];
    }

    /**
     * @return array
     */
    public function attributes() : array
    {
        return array_merge(parent::attributes(), [
            //
        ]);
    }
}
