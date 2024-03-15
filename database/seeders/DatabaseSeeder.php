<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Database Seeder
 *
 * @package Database\Seeders
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() : void
    {
        /*
         * Seeder for "User" and related models tables
         */
        \App\Models\Permission::factory()
            ->count(50)
            ->create();

        \App\Models\Role::factory()
            ->count(10)
            ->create();

        \App\Models\User::factory()
            ->count(50)
            ->create();
    }
}
