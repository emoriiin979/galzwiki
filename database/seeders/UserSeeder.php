<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => '南雲 えもり',
            'email' => 'emoriiin979@gmail.com',
            'password' => bcrypt('password'),
        ]);
    }
}
