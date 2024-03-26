<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'first_name' => 'User1',
            'last_name' => 'Test',
            'email' => 'user1@test.ru',
            'password' => 'Qa1'
        ]);
        User::create([
            'first_name' => 'User2',
            'last_name' => 'Test',
            'email' => 'user2@test.ru',
            'password' => 'As2'
        ]);
    }
}
