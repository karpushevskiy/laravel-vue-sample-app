<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace Database\Factories;

use Illuminate\Support\Str;

/**
 * Permission Factory
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 * @package Database\Factories
 */
class PermissionFactory extends BaseFactory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition() : array
    {
        $name = $this->faker->unique()->domainWord;

        return [
            'name'       => Str::slug($name),
            'human_name' => $name,
            'guard_name' => config('auth.defaults.guard'),
        ];
    }
}
