<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Services\CareerServices\AiServices as AiServices;
use Illuminate\Http\Request;

class CareerAiController extends Controller
{
    public function newCareer(Request $request, AiServices $aiService)
    {
        $request->validate([
            'careerTitle' => 'required|string|max:255'
        ]);

        try {
            $aiService->generateCareer($request->careerTitle);

            return back()->with('success', 'Career generated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
