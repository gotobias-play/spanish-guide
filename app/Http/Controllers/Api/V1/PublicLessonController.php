<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class PublicLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lesson::where('is_published', true);

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return $query->orderBy('order')->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return $lesson->load('quizzes');
    }
}
