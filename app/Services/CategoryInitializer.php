<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryInitializer
{
    /**
     * Ensure default categories exist for the user.
     */
    public function ensureDefaults(User $user): void
    {
        $defaults = [
            ['name' => 'Salary', 'kind' => 'income', 'color' => '#22c55e'],
            ['name' => 'Mortgage/Rent', 'kind' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Utilities', 'kind' => 'expense', 'color' => '#f97316'],
            ['name' => 'Credit Card Payment', 'kind' => 'expense', 'color' => '#f43f5e'],
            ['name' => 'Insurance', 'kind' => 'expense', 'color' => '#fb923c'],
            ['name' => 'Subscriptions', 'kind' => 'expense', 'color' => '#fb7185'],
            ['name' => 'Groceries', 'kind' => 'expense', 'color' => '#f59e0b'],
        ];

        foreach ($defaults as $default) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $default['name'],
                    'kind' => $default['kind'],
                ],
                [
                    'color' => $default['color'],
                ]
            );
        }
    }
}
