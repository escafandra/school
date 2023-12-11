<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::factory(25)
            ->published()
            ->has(Lesson::factory(25)->published())
            ->create();
    }
}
