<?php

namespace App\Infolists\Components;

use Filament\Infolists\Components\Component;

class CompleteButton extends Component
{
    protected string $view = 'infolists.components.complete-button';

    public static function make(): static
    {
        return app(static::class);
    }
}
