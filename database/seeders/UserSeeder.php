<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'name' => '佐藤美咲',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([
            'name' => '高橋健太',
            'email' => 'takahashi@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
