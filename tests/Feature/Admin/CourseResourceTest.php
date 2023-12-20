<?php

use App\Filament\Resources\CourseResource;
use App\Filament\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Resources\CourseResource\Pages\ListCourses;
use App\Models\Course;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $this->get(CourseResource::getUrl())->assertSuccessful();
});

it('can list courses', function () {
    $courses = Course::factory()->count(10)->create();

    livewire(ListCourses::class)
        ->assertCanSeeTableRecords($courses);
});

it('can render create page', function () {
    $this->get(CourseResource::getUrl('create'))->assertSuccessful();
});

it('can create a course', function () {
    $newData = Course::factory()->published()->make();

    livewire(CreateCourse::class)
        ->fillForm([
            'title'        => $newData->title,
            'description'  => $newData->description,
            'is_published' => $newData->is_published,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Course::class, [
        'title'        => $newData->title,
        'description'  => $newData->description,
        'is_published' => $newData->is_published,
    ]);
});

it('can validate create input', function () {
    livewire(CreateCourse::class)
        ->fillForm([
            'title' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can render edit page', function () {
    $this->get(CourseResource::getUrl('edit', [
        'record' => Course::factory()->create(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $course = Course::factory()->create();

    livewire(EditCourse::class, [
        'record' => $course->getRouteKey(),
    ])
        ->assertFormSet([
            'title'        => $course->title,
            'description'  => $course->description,
            'is_published' => $course->is_published,
        ]);
});

it('can save a course', function () {
    $course  = Course::factory()->create();
    $newData = Course::factory()->published()->make();

    livewire(EditCourse::class, [
        'record' => $course->getRouteKey(),
    ])
        ->fillForm([
            'title'        => $newData->title,
            'description'  => $newData->description,
            'is_published' => $newData->is_published,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($course->refresh())
        ->title->toBe($newData->title)
        ->description->toBe($newData->description)
        ->is_published->toBe($newData->is_published);
});

it('can validate edit input', function () {
    $course = Course::factory()->create();

    livewire(EditCourse::class, [
        'record' => $course->getRouteKey(),
    ])
        ->fillForm([
            'title' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can delete a course', function () {
    $course = Course::factory()->create();

    livewire(EditCourse::class, [
        'record' => $course->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($course);
});
