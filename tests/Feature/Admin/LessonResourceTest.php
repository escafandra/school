<?php

use App\Filament\Resources\CourseResource;
use App\Filament\Resources\LessonResource;
use App\Filament\Resources\LessonResource\Pages\CreateLesson;
use App\Filament\Resources\LessonResource\Pages\EditLesson;
use App\Filament\Resources\LessonResource\Pages\ListLessons;
use App\Models\Course;
use App\Models\Lesson;
use Filament\Actions\DeleteAction;

use function Pest\Livewire\livewire;

it('can render list page', function () {
    $parent = Course::factory()->create();
    $this->get(CourseResource::getUrl('lessons.index', ['parent' => $parent->id]))
        ->assertSuccessful();
});

it('can list lessons', function () {
    $parent = Course::factory()
        ->has(Lesson::factory()->count(10))
        ->create();

    livewire(ListLessons::class, [
        'parent' => $parent,
    ])
        ->assertCanSeeTableRecords($parent->lessons);
});

it('can render create page', function () {
    $parent = Course::factory()->create();
    $this->get(CourseResource::getUrl('lessons.create', ['parent' => $parent->id]))
        ->assertSuccessful();
});

it('can create a lesson', function () {
    $parent  = Course::factory()->create();
    $newData = Lesson::factory()->published()->make([
        'course_id' => $parent->id,
        'position'  => 1,
    ]);

    livewire(CreateLesson::class, [
        'parent' => $parent,
    ])
        ->fillForm([
            'title'        => $newData->title,
            'position'     => $newData->position,
            'lesson_text'  => $newData->lesson_text,
            'is_published' => $newData->is_published,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Lesson::class, [
        'title'        => $newData->title,
        'position'     => $newData->position,
        'lesson_text'  => $newData->lesson_text,
        'is_published' => (int) $newData->is_published,
        'course_id'    => $newData->course_id,
    ]);
});

it('can validate create input', function () {
    livewire(CreateLesson::class)
        ->fillForm([
            'title' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can render edit page', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();

    $this->get(CourseResource::getUrl('lessons.edit', [
        'parent' => $parent,
        'record' => $parent->lessons->first(),
    ]))->assertSuccessful();
});

it('can retrieve data', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();
    $lesson = $parent->lessons->first();

    livewire(EditLesson::class, [
        'parent' => $parent,
        'record' => $lesson->getRouteKey(),
    ])
        ->assertFormSet([
            'title'        => $lesson->title,
            'position'     => $lesson->position,
            'lesson_text'  => $lesson->lesson_text,
            'is_published' => (int) $lesson->is_published,
            'course_id'    => $lesson->course_id,
        ]);
});

it('can save a lesson', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();

    $newData = Lesson::factory()
        ->published()
        ->make([
            'course_id' => $parent->id,
        ]);

    livewire(EditLesson::class, [
        'parent' => $parent,
        'record' => $parent->lessons()->first()->getRouteKey(),
    ])
        ->fillForm([
            'title'        => $newData->title,
            'position'     => $newData->position,
            'lesson_text'  => $newData->lesson_text,
            'is_published' => (int) $newData->is_published,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($parent->lessons()->first()->refresh())
        ->title->toBe($newData->title)
        ->positon->toBe($newData->positon)
        ->lesson_text->toBe($newData->lesson_text)
        ->course_id->toBe($newData->course_id)
        ->is_published->toBe($newData->is_published);
});

it('can validate edit input', function () {
    $parent = Course::factory()
        ->has(Lesson::factory())
        ->create();

    livewire(EditLesson::class, [
        'parent' => $parent,
        'record' => $parent->lessons->first()->getRouteKey(),
    ])
        ->fillForm([
            'title' => null,
        ])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can delete a lesson', function () {
    $parent = Course::factory()
        ->has(Lesson::factory()->count(10))
        ->create();

    $lesson = $parent->lessons->first();

    livewire(EditLesson::class, [
        'parent' => $parent,
        'record' => $lesson->getRouteKey(),
    ])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($lesson);
});
