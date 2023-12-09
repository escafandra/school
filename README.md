## Learning Management System

This project demonstrates how to create a simple learning management system for managing courses and lessons.

The repository contains the complete Laravel + Filament project to demonstrate functionality, including migrations/seeds for the demo data.

Please pick the parts that you need in your projects.

---
## How to Install

- Clone the repository with `git clone`
- Copy the `.env.example` file to `.env` and edit database credentials there
- Run `composer install`
- Run `php artisan key:generate`
- Run `php artisan migrate:fresh --seed` (it has some seeded data for your testing)
- Run `php artisan storage:link`
- To manage courses and lessons, visit the `/admin` URL and log in with credentials `admin@admin.com` and `password`
- To browse courses and complete lessons, log in as a student via the `/student` URL and login with credentials `student@admin.com` and `password`

---
## Screenshots

![](https://laraveldaily.com/uploads/2023/10/lms-01.png)

![](https://laraveldaily.com/uploads/2023/10/lms-02.png)

![](https://laraveldaily.com/uploads/2023/10/lms-03.png)

![](https://laraveldaily.com/uploads/2023/10/lms-04.png)

![](https://laraveldaily.com/uploads/2023/10/lms-05.png)

---
## How It Works

The project has three main parts:

- Filament Admin Panel
    - Courses
    - Lessons are managed inside the course as a nested resource
    - Course has a featured image using Spatie Media Library
    - Course `description` has a rich text editor
    - Lesson `lesson_text` has a rich text editor
    - Ability to reorder lessons in the lesson list
    - Course and Lessons can be published/unpublished
- Filament Student Panel
    - Students can browse courses and lessons
    - Lesson can be marked/unmarked as completed
    - When the student navigates to the next lesson, the current lesson is automatically marked as completed
    - Students can see their courses in progress on the `My Courses` page
- Public page
    - Lists published courses and lessons.
    - To view the lesson, the user must register/login
    - After registration/login, students are redirected to the lesson page if they tried to view it before.

Lesson pages have the `HasParentResource` trait:

**app/Filament/Traits/HasParentResource.php**

```php
namespace App\Filament\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasParentResource
{
    public Model|int|string|null $parent = null;

    public function bootHasParentResource(): void
    {
        if ($parent = (request()->route('parent') ?? request()->input('parent'))) {
            $parentResource = $this->getParentResource();

            $this->parent = $parentResource::resolveRecordRouteBinding($parent);

            if (! $this->parent) {
                throw new ModelNotFoundException();
            }
        }
    }

    public function getParentResource(): string
    {
        if (! isset(static::$parentResource)) {
            throw new Exception('Parent resource is not set for ' . static::class);
        }

        return static::$parentResource;
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        return $query->where(str($this->parent?->getTable())->singular()->append('_id'), $this->parent->getKey());
    }

    public function getBreadcrumbs(): array
    {
        $resource       = $this->getResource();
        $parentResource = $this->getParentResource();

        $breadcrumbs = [
            $parentResource::getUrl() => $parentResource::getBreadCrumb(),
            '#parent'                 => $parentResource::getRecordTitle($this->parent),
        ];

        // Breadcrumb to child.index or parent.view
        $childIndex = $resource::getPluralModelLabel() . '.index';
        $parentView = 'view';

        if ($parentResource::hasPage($childIndex)) {
            $url               = $parentResource::getUrl($childIndex, ['parent' => $this->parent]);
            $breadcrumbs[$url] = $resource::getBreadCrumb();
        } elseif ($parentResource::hasPage($parentView)) {
            $url               = $parentResource::getUrl($parentView, ['record' => $this->parent]);
            $breadcrumbs[$url] = $resource::getBreadCrumb();
        }

        if (isset($this->record)) {
            $breadcrumbs['#'] = $resource::getRecordTitle($this->record);
        }

        $breadcrumbs[] = $this->getBreadCrumb();

        return $breadcrumbs;
    }
}
```

Course resource has custom action to manage lessons and has routes for lesson pages.

**app/Filament/Resources/CourseResource.php**

```php
namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages\CreateCourse;
use App\Filament\Resources\CourseResource\Pages\EditCourse;
use App\Filament\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Resources\LessonResource\Pages\CreateLesson;
use App\Filament\Resources\LessonResource\Pages\EditLesson;
use App\Filament\Resources\LessonResource\Pages\ListLessons;
use App\Models\Course;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record->title;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('title')->required(),
                        RichEditor::make('description'),
                        Checkbox::make('is_published'),
                    ])
                    ->columnSpan(2),
                Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('Featured Image')
                            ->collection('featured_image'),
                    ]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('Featured Image')
                    ->collection('featured_image'),
                TextColumn::make('title'),
                TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Manage lessons')
                    ->color('success')
                    ->icon('heroicon-m-academic-cap')
                    ->url(fn (Course $record): string => self::getUrl('lessons.index', [
                        'parent' => $record->id,
                    ])),
                EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'edit'   => EditCourse::route('/{record}/edit'),

            // Lessons
            'lessons.index'  => ListLessons::route('/{parent}/lessons'),
            'lessons.create' => CreateLesson::route('/{parent}/lessons/create'),
            'lessons.edit'   => EditLesson::route('/{parent}/lessons/{record}/edit'),
        ];
    }

    public static function getUrl(string $name = 'index', array $parameters = [], bool $isAbsolute = true, string $panel = null, Model $tenant = null): string
    {
        $parameters['tenant'] ??= ($tenant ?? Filament::getTenant());

        $routeBaseName = static::getRouteBaseName(panel: $panel);
        $routeFullName = "{$routeBaseName}.{$name}";
        $routePath     = Route::getRoutes()->getByName($routeFullName)->uri();

        if (str($routePath)->contains('{parent}')) {
            $parameters['parent'] ??= (request()->route('parent') ?? request()->input('parent'));
        }

        return route($routeFullName, $parameters, $isAbsolute);
    }
}
```

Here's a resource to display the `My Courses` table.

**app/Filament/Student/Resources/MyCoursesResource.php**

```php
namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\MyCoursesResource\Pages\ListMyCourses;
use App\Models\Course;
use App\Tables\Columns\ProgressColumn;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MyCoursesResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static ?string $navigationLabel = 'My Courses';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(1)
                    ->schema([
                        SpatieMediaLibraryImageColumn::make('Featured Image')
                            ->collection('featured_image')
                            ->extraImgAttributes(['class' => 'w-full rounded'])
                            ->height('auto'),
                        TextColumn::make('title')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextColumnSize::Large),
                        TextColumn::make('description')
                            ->html(),
                        ProgressColumn::make('Progress'),
                    ]),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 18, 27])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->published()
                ->whereIn('id', auth()->user()->courses()->pluck('id')))
            ->recordUrl(fn (Model $model) => CourseResource::getUrl('view', [$model]));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyCourses::route('/'),
        ];
    }
}
```

The lesson Model implements functionality to complete and retrieve the previous/next lesson.

**app/Models/Lesson.php**

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'position',
        'lesson_text',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getNext(): ?self
    {
        $lessons = $this->course->publishedLessons()->get();

        $currentIndex = $lessons->search(fn (Lesson $lesson) => $lesson->is($this));

        if ($currentIndex === $lessons->keys()->last()) {
            return null;
        }

        return $lessons[$currentIndex + 1];
    }

    public function getPrevious(): ?self
    {
        $lessons = $this->course->publishedLessons()->get();

        $currentIndex = $lessons->search(fn (Lesson $lesson) => $lesson->is($this));

        if ($currentIndex === 0) {
            return null;
        }

        return $lessons[$currentIndex - 1];
    }

    public function isCompleted(): bool
    {
        return auth()->user()->completedLessons->containsStrict('id', $this->id);
    }

    public function markAsCompleted(): self
    {
        if ($this->isCompleted()) {
            return $this;
        }

        auth()->user()->completeLesson($this);
        auth()->user()->refresh();

        return $this;
    }

    public function markAsUncompleted(): self
    {
        if (! $this->isCompleted()) {
            return $this;
        }

        auth()->user()->uncompleteLesson($this);
        auth()->user()->refresh();

        return $this;
    }
}
```

User Model provides reusable methods to attach/detach completed lessons.

**app/Models/User.php**

```php
public function completedLessons(): BelongsToMany
{
    return $this->belongsToMany(Lesson::class)->published();
}

public function courses(): BelongsToMany
{
    return $this->belongsToMany(Course::class);
}

public function completeLesson(Lesson $lesson): void
{
    $this->completedLessons()->attach($lesson);
    $this->courses()->syncWithoutDetaching($lesson->course_id);
}

public function uncompleteLesson(Lesson $lesson): void
{
    $this->completedLessons()->detach($lesson);
    $courseLessons = $lesson->course->lessons()->pluck('id')->toArray();

    if (! $this->completedLessons()->whereIn('id', $courseLessons)->exists()) {
        $this->courses()->detach($lesson->course_id);
    }
}
```

The lesson view page has custom Infolist components to display the Lessons list and progress.

**resources/views/infolists/components/list-lessons.blade.php**

```blade
<div {{ $attributes }}>
    <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
            {{ $getName() }}
        </span>
    </dt>
    <div class="flex flex-col space-y-0.5 mt-2">
        @foreach($getLessons() as $index => $lesson)
            <div @class([
                    'rounded px-2',
                    'bg-primary-600 text-white' => $isActive($lesson),
                    'hover:bg-gray-100 hover:text-primary-600' => !$isActive($lesson)
                ])>
                <a href="{{ $getUrl($lesson) }}" class="flex flex-row">
                    <div class="w-5 mr-2 text-right shrink-0 font-mono">
                        {{ $index + 1 }}
                    </div>
                    <div>
                        {{ $lesson->title }}
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
```

**resources/views/infolists/components/course-progress.blade.php**

```blade
<div {{ $attributes }}>
    <dt class="fi-in-entry-wrp-label inline-flex items-center gap-x-3">
        <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
            {{ $getProgress() }} / {{ $getProgressMax() }} lessons finished ({{ $getPercentage() }}%)
        </span>
    </dt>
    <progress
        class="w-full rounded-full shadow-inner"
        id="progress"
        value="{{ $getProgress() }}"
        max="{{ $getProgressMax() }}"
    >
        {{ $getPercentage() }}%
    </progress>
</div>
```
