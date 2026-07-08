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

    public function careerByUser(Request $request, CareerService $careerService)
    {
        //dd(Auth::user());
        $careers = $careerService->careerByUser(1); //Add user id later

        return response()->json($careers);
    }

    public function all(Request $request, CareerService $careerService)
    {

        $careers = $careerService->all(); //Add user id later

        return response()->json($careers);
    }

    public function update(Request $request, int $careerID, CareerService $careerService)
    {
        $career = $careerService->update($careerID, $request->all());

        return response()->json([
            'message' => 'Career updated successfully.',
            'data' => $career
        ]);
    }

    public function delete(int $careerID, CareerService $careerService)
    {

        $careers = $careerService->delete($careerID); //Add user id later

        return response()->json($careers);
    }
}
