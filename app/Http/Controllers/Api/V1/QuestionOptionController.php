<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QuestionOption;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->has('question_id')) {
            return QuestionOption::where('question_id', $request->question_id)->get();
        }
        return QuestionOption::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_text' => 'required|string',
            'is_correct' => 'boolean',
        ]);

        $option = QuestionOption::create($validated);

        return response()->json($option, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(QuestionOption $questionOption)
    {
        return $questionOption;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuestionOption $questionOption)
    {
        $validated = $request->validate([
            'option_text' => 'sometimes|required|string',
            'is_correct' => 'sometimes|boolean',
        ]);

        $questionOption->update($validated);

        return response()->json($questionOption);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuestionOption $questionOption)
    {
        $questionOption->delete();

        return response()->json(null, 204);
    }
}
