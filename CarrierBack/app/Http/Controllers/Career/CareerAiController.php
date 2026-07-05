<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Services\CareerServices\AiServices as AiServices;
use Illuminate\Http\Request;

class CareerAiController extends Controller
{
    public function newCareer(Request $request)
    {
        $careerTitle = $request->input('careerTitle');

        $aiService = new AiServices();

        $career = $aiService->generateCareer($careerTitle);
        return back()->with('success', 'Career generated successfully!');
        //return response()->json($career);
    }
}
