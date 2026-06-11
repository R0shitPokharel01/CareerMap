<?php
namespace App\Services;

use App\Models\Careers;
use App\Models\Phases;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AiServices{
    public function generateCareer(string $title){

        $slug = Str::slug($title);
        $existing = Careers::where('slug',$slug)->first();

        if($existing){
            return $existing;
        }

        $aiResponse = $this->callAI($title);

        return $this->save($aiResponse);

    }

    public function callAI(string $title){
        return ['title' => $title];
    }

    public function save(array $aiResponse){

        return DB::transaction(function ($aiResponse){
            //storing data into Career , phases , resources
            $career = Careers::create([

            ]);

            foreach ($aiResponse['phases'] as $phase){

                $phaseData = Phases::create([

                ]);
            }

            foreach ($aiResponse['resources'] as $resource){

            }

        });

    }






}



?>
