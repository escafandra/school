<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->admin()
            ->create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
            ]);

        User::factory()
            ->create([
                'name' => 'Student',
                'email' => 'student@admin.com',
            ]);

        Course::factory(25)
            ->published()
            ->has(Lesson::factory(25)->published())
            ->create();
    }
}
