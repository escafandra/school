<?php

namespace App\Http\Controllers;

use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        return view('home', [
            'courses' => Course::published()
                ->with('media')
                ->paginate(9),
        ]);
    }

    public function show(Course $course)
    {
        abort_unless($course->is_published, 404);

        return view('course', [
            'course'  => $course,
            'lessons' => $course->publishedLessons,
        ]);
    }
}
