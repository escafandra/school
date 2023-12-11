<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->admin()
            ->create([
                'name'  => 'Admin',
                'email' => 'admin@escafandra.tech',
            ]);

        User::factory()
            ->create([
                'name'  => 'Student',
                'email' => 'student@escafandra.tech',
            ]);
    }
}
