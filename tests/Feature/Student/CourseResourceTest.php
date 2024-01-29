<?php

use App\Filament\Student\Resources\CourseResource;
use App\Filament\Student\Resources\CourseResource\Pages\ListCourses;
use App\Models\Course;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(CourseResource::getUrl())->assertSuccessful();
});

it('can list courses', function () {
    $courses = Course::factory()->count(10)->published()->create();

    livewire(ListCourses::class)
        ->assertCanSeeTableRecords($courses);
});

it('cannot list not published courses', function () {
    $courses = Course::factory()->count(10)->create();

    livewire(ListCourses::class)
        ->assertCanNotSeeTableRecords($courses);
});

it('can render view page', function () {
    $this->get(CourseResource::getUrl('view', [
        'record' => Course::factory()->published()->create(),
    ]))->assertSuccessful();
});
