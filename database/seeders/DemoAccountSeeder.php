<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $user = User::first();

        if (! $user || $user->accounts()->exists()) {
            return;
        }

        Account::create([
            'user_id' => $user->id,
            'name' => 'Checking',
            'type' => 'cash',
            'currency' => 'USD',
        ]);

        Account::create([
            'user_id' => $user->id,
            'name' => 'Savings',
            'type' => 'cash',
            'currency' => 'USD',
        ]);

        $card = Account::create([
            'user_id' => $user->id,
            'name' => 'Credit Card',
            'type' => 'credit_card',
            'currency' => 'USD',
        ]);

        $card->creditCard()->create([
            'due_day' => 15,
            'statement_close_day' => 8,
            'autopay' => true,
            'autopay_account_id' => $user->accounts()->first()?->id,
            'notes' => 'Demo autopay setup',
        ]);
    }
}
