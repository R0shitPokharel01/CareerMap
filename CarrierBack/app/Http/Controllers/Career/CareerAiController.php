<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Services\CareerServices\AiServices as AiServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerAiController extends Controller
{
    public function newCareer(Request $request, AiServices $aiService)
    {
        $request->validate([
            'careerTitle' => 'required|string|max:255'
        ]);

        try {
            $career = $aiService->generateCareer($request->careerTitle, 1);

            return response()->json([
                'success' => true,
                'message' => 'Career roadmap generated successfully.',
                'data'    => $career,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
