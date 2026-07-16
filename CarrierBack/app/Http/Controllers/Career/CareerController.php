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
        try {
            $careers = $careerService->search($request->input('search'));
            // dd($request->all());

            if ($careers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No ' . $request->input('search') . ' found.',
                ], 404);
            }
            return response()->json($careers);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search ' . $request->input('search') . '.',
            ], 500);
        }
    }

    public function careerByUser(CareerService $careerService)
    {
        $careers = $careerService->careerByUser(Auth::id());

        return response()->json($careers);
    }

    public function all(CareerService $careerService)
    {
        return response()->json(
            $careerService->all()
        );
    }

    public function update(Request $request, int $careerID, CareerService $careerService)
    {
        $validated = $request->validate([
            // Add  validation rules

        ]);

        $career = $careerService->update($careerID, $validated);

        return response()->json([
            'message' => 'Career updated successfully.',
            'data' => $career
        ]);
    }

    public function delete(int $careerID, CareerService $careerService)
    {
        $careerService->delete($careerID);

        return response()->json([
            'message' => 'Career deleted successfully.'
        ]);
    }
}