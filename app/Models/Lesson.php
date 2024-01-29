<?php

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

        $currentIndex = (int) $lessons->search(fn (Lesson $lesson) => $lesson->is($this));

        if ($currentIndex === (int) $lessons->keys()->last()) {
            return null;
        }

        return $lessons[$currentIndex + 1];
    }

    public function getPrevious(): ?self
    {
        $lessons = $this->course->publishedLessons()->get();

        $currentIndex = (int) $lessons->search(fn (Lesson $lesson) => $lesson->is($this));

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
