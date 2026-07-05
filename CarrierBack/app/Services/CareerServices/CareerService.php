<?php

namespace App\Services\CareerServices;

use App\Models\Careers;

class CareerService
{
    public function search(?string $search)
    {
        return Careers::query()
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
        return Careers::query()
            ->where('user_id', $userId)
            ->get();
    }
}
