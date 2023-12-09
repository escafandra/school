<?php

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
