<?php

namespace App\Filament\Resources;

use App\Models\Lesson;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static ?string $modelLabel = 'Lección';

    protected static ?string $pluralModelLabel = 'Lecciones';

    protected static bool $shouldRegisterNavigation = false;

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record->title;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->columnSpanFull()
                    ->required()
                    ->label('Nombre'),
                RichEditor::make('lesson_text')
                    ->columnSpanFull()
                    ->label('Contenido'),
                Checkbox::make('is_published')
                    ->label('Está publicada'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('Posición'),
                TextColumn::make('title')
                    ->label('Nombre'),
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('position')
            ->reorderable('position');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
}
