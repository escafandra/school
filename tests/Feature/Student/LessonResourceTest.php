<?php

use App\Filament\Student\Resources\CourseResource;
use App\Filament\Student\Resources\LessonResource\Pages\ViewLesson;
use App\Models\Course;
use App\Models\Lesson;

use function Pest\Livewire\livewire;

it('can render view page', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();

    $this->get(CourseResource::getUrl('lessons.view', [
        'parent' => $parent,
        'record' => $parent->lessons->first(),
    ]))->assertSuccessful();
});

it('can mark lesson as complete', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();

    livewire(ViewLesson::class, [
        'parent' => $parent,
        'record' => $parent->lessons->first()->getRouteKey(),
    ])
        ->call('toggleCompleted')
        ->assertHasNoErrors();
});
