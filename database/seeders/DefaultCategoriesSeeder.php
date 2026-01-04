<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\CategoryInitializer;
use Illuminate\Database\Seeder;

class DefaultCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $initializer = new CategoryInitializer();

        User::all()->each(function (User $user) use ($initializer) {
            $initializer->ensureDefaults($user);
        });
    }
}
