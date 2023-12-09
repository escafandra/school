<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function publishedLessons(): HasMany
    {
        return $this->lessons()->published();
    }

    public function progress(): array
    {
        $lessons   = $this->publishedLessons;
        $completed = auth()->user()->completedLessons()
            ->whereIn('lesson_id', $lessons->pluck('id'))
            ->count();

        return [
            'value'      => $completed,
            'max'        => $lessons->count(),
            'percentage' => (int) floor(($completed / max(1, $lessons->count())) * 100),
        ];
    }
}
