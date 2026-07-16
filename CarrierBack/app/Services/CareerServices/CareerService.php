<?php

namespace App\Services\CareerServices;

use App\Models\Careers;

class CareerService
{

    //Search career by title , description , category and skills
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

    // List careers by user
    public function careerByUser(int $userId)
    {
        return Careers::with('phases.resources')
            ->where('user_id', $userId)
            ->get();
    }

    // List all Careers
    public function all()
    {
        return Careers::with('phases.resources')->paginate(10);
    }


    // Delete career
    public function delete(int $id)
    {
        $career = Careers::findOrFail($id);

        $career->delete();

        return "Career Deleted Successfully";
    }

    //Update Career
    public function update(int $id, array $data)
    {
        $career = Careers::findOrFail($id);

        $career->update($data);

        return $career;
    }
}
