<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title'       => rtrim(fake()->sentence(), '.'),
            'description' => fake()->realText(),
        ];
    }

    public function published(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_published' => true,
            ];
        });
    }

    public function configure(): static
    {
        $images = collect(Storage::files('demo-images'));

        return $this->afterCreating(function (Course $course) use ($images) {
            $course->addMediaFromDisk($images->random())
                ->preservingOriginal()
                ->toMediaCollection('featured_image');
        });
    }
}
