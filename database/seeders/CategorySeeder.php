<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Salary', 'type' => 'income'],
            ['name' => 'Utilities', 'type' => 'expense'],
            ['name' => 'Mortgage/Rent', 'type' => 'expense'],
            ['name' => 'Credit Card Payment', 'type' => 'expense'],
            ['name' => 'Insurance', 'type' => 'expense'],
            ['name' => 'Subscriptions', 'type' => 'expense'],
            ['name' => 'Groceries', 'type' => 'expense'],
        ];

        User::all()->each(function (User $user) use ($defaults) {
            foreach ($defaults as $category) {
                Category::firstOrCreate(
                    ['user_id' => $user->id, 'name' => $category['name']],
                    ['type' => $category['type']]
                );
            }
        });
    }
}
