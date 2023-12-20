<?php

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

    protected static ?string $modelLabel = 'Curso';

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
                        TextInput::make('title')
                            ->required()
                            ->label('Nombre'),
                        RichEditor::make('description')
                            ->label('Descripción'),
                        Checkbox::make('is_published')
                            ->label('Está Publicado'),
                    ])
                    ->columnSpan(2),
                Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('Featured Image')
                            ->collection('featured_image')
                            ->label('Imagen'),
                    ]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('Featured Image')
                    ->collection('featured_image')
                    ->label('Imagen'),
                TextColumn::make('title')
                    ->label('Nombre'),
                TextColumn::make('lessons_count')
                    ->counts('lessons')
                    ->badge()
                    ->label('Número de lecciones'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Lecciones')
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
