<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicQuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Cache::remember('public_quizzes_with_questions', 3600, function () {
            return Quiz::with('questions.options')->get();
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        return Cache::remember("public_quiz_{$quiz->id}_with_questions", 3600, function () use ($quiz) {
            return $quiz->load('questions.options');
        });
    }
}
