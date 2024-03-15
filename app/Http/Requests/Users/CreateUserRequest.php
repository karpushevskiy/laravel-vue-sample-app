<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

/**
 * Create User Request
 *
 * @package \App\Http\Requests\Users
 */
class CreateUserRequest extends BaseFormRequest
{
    /**
     * @return array
     */
    public function rules() : array
    {
        $roles = config('permission.project_roles');

        return [
            'first_name' => array_merge($this->textRules(), ['required']),
            'last_name'  => array_merge($this->textRules(), ['nullable']),
            'email'      => ['required', 'email', Rule::unique(User::class)],
            'password'   => array_merge(['required'], $this->passwordRules()),
            'role'       => ['required', $this->roleExistRule($roles)],
        ];
    }

    /**
     * @return array
     */
    public function attributes() : array
    {
        return [
            //
        ];
    }
}
