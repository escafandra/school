<?php

namespace App\Observers;

use App\Models\Lesson;
use Illuminate\Support\Facades\DB;

class LessonObserver
{
    public function creating(Lesson $lesson): void
    {
        $lesson->position = (int) Lesson::where('course_id', $lesson->course_id)
            ->max('position') + 1;
    }

    public function deleted(Lesson $lesson): void
    {
        $ordered = Lesson::where('course_id', $lesson->course_id)
            ->orderBy('position')
            ->pluck('id');

        if ($ordered->count() > 1) {
            $orderColumn = 'position';
            $keyName     = $lesson->getKeyName();

            $cases = collect($ordered)
                ->map(fn ($key, int $index) => sprintf(
                    'when %s = %s then %d',
                    $keyName,
                    DB::getPdo()->quote($key),
                    $index + 1
                ))
                ->implode(' ');

            Lesson::query()
                ->whereIn('id', $ordered)
                ->update([
                    $orderColumn => DB::raw('case ' . $cases . ' end'),
                ]);
        }
    }
}
