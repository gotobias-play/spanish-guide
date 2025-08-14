<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Cache::remember('public_courses_with_lessons', 3600, function () {
            return Course::with(['lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('order');
            }])->where('is_published', true)->get();
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        return Cache::remember("public_course_{$course->id}_with_lessons", 3600, function () use ($course) {
            return $course->load(['lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('order');
            }]);
        });
    }
}
