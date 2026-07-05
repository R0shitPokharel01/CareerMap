<?php

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Services\CareerServices\CareerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerController extends Controller
{
    public function search(Request $request, CareerService $careerService)
    {
        $careers = $careerService->search($request->input('search'));

        return response()->json($careers);
    }

    public function CareerByUser(Request $request, CareerService $careerService)
    {
        //dd(Auth::user());
        $careers = $careerService->careerByUser(1);

        return response()->json($careers);
    }
}
