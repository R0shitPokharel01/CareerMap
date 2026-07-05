<?php

namespace App\Services\CareerServices;

use App\Models\Careers;

class CareerService
{
    public function search(?string $search)
    {
        return Careers::with('phases.resources')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('category', 'LIKE', "%{$search}%")
                    ->orWhere('skills', 'LIKE', "%{$search}%");
            })
            ->get();
    }

    public function careerByUser(int $userId)
    {
        return Careers::with('phases.resources')
            ->where('user_id', $userId)
            ->get();
    }

    public function all()
    {
        return Careers::with('phases.resources')->get();
    }
}
