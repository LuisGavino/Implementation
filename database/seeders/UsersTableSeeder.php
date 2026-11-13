<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Admin account
        $admin = User::firstOrNew([
            'email' => 'admin@example.com'
        ]);

        $admin->name = 'Lakbay Pilipinas Admin';
        $admin->password = Hash::make('password');
        $admin->role = User::ROLE_ADMIN;
        $admin->save();

        // Customer account
        $customer = User::firstOrNew([
            'email' => 'user@example.com'
        ]);

        $customer->name = 'Lakbay Pilipinas Customer';
        $customer->password = Hash::make('password');
        $customer->role = User::ROLE_CUSTOMER;
        $customer->save();
    }
}
