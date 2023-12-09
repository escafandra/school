<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Component;

class LessonPaginator extends Component
{
    protected string $view = 'infolists.components.lesson-paginator';

    protected $currentLesson;

    public static function make(): static
    {
        return app(static::class);
    }

    public function currentLesson($lesson)
    {
        $this->currentLesson = $lesson;

        return $this;
    }

    public function getCurrentLesson()
    {
        return $this->currentLesson;
    }
}
