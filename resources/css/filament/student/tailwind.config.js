import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Student/**/*.php',
        './resources/views/filament/student/**/*.blade.php',
        './resources/views/infolists/**/*.blade.php',
        './resources/views/tables/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
