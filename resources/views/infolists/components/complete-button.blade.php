<div {{ $attributes }}>
    <x-filament::button
        wire:click="toggleCompleted"
    >
        {{ $getRecord()->isCompleted() ? 'Marcar como incompleto' : 'Marcar como completado' }}
    </x-filament::button>
</div>
