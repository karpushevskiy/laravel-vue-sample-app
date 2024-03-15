<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Http\Traits;

use Illuminate\Validation\Rule;

/**
 * Trait for returning prepared "exist" validation rules
 *
 * @package \App\Http\Traits
 */
trait ExistValidationRules
{
    /**
     * @param string $table
     * @param string $column
     * @return \Illuminate\Validation\Rules\Exists
     */
    protected function existByColumnRule(string $table, string $column = 'id') : \Illuminate\Validation\Rules\Exists
    {
        return Rule::exists($table, $column);
    }

    /**
     * @return \Illuminate\Validation\Rules\Exists
     */
    protected function userExistRule() : \Illuminate\Validation\Rules\Exists
    {
        return $this->existByColumnRule('users');
    }

    /**
     * @param array|string|null $names
     * @return \Illuminate\Validation\Rules\Exists
     */
    protected function roleExistRule(array|string|null $names) : \Illuminate\Validation\Rules\Exists
    {
        $rule = $this->existByColumnRule('roles');

        if ($names) {
            $rule->whereIn('name', (array) $names);
        }

        return $rule;
    }
}
