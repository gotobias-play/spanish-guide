<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserQuizProgress;
use Illuminate\Support\Facades\Auth;

class UserQuizProgressController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|string',
            'score' => 'nullable|integer',
            'data' => 'nullable|json',
        ]);

        $progress = UserQuizProgress::updateOrCreate(
            ['user_id' => Auth::id(), 'section_id' => $request->section_id],
            ['score' => $request->score, 'data' => $request->data]
        );

        return response()->json($progress, 200);
    }

    public function show($section_id)
    {
        $progress = UserQuizProgress::where('user_id', Auth::id())
                                    ->where('section_id', $section_id)
                                    ->firstOrFail();

        return response()->json($progress, 200);
    }

    public function index()
    {
        $progress = UserQuizProgress::where('user_id', Auth::id())->get();
        return response()->json($progress, 200);
    }
}