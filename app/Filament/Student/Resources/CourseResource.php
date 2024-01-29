<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\CourseResource\Pages\ListCourses;
use App\Filament\Student\Resources\CourseResource\Pages\ViewCourse;
use App\Filament\Student\Resources\LessonResource\Pages\ViewLesson;
use App\Models\Course;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $modelLabel = 'Curso';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return 'Todos los Cursos';
    }

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
                            ->height('auto')
                            ->label('Imagen'),
                        TextColumn::make('title')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextColumnSize::Large)
                            ->label('Nombre'),
                        TextColumn::make('description')
                            ->html()
                            ->label('Descripción'),
                    ]),
            ])
            ->contentGrid(['md' => 2, 'xl' => 3])
            ->paginationPageOptions([9, 18, 27])
            ->defaultSort('id', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->published());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'view'  => ViewCourse::route('/{record}'),

            'lessons.view' => ViewLesson::route('/{parent}/lessons/{record}'),
        ];
    }

    public static function getUrl(string $name = 'index', array $parameters = [], bool $isAbsolute = true, ?string $panel = 'student', Model $tenant = null): string
    {
        $parameters['tenant'] ??= ($tenant ?? Filament::getTenant());

        $routeBaseName = static::getRouteBaseName(panel: $panel);

        return route("{$routeBaseName}.{$name}", $parameters, $isAbsolute);
    }
}
