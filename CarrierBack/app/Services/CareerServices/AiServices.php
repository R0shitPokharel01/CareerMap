<?php

namespace App\Services\CareerServices;

use App\Models\Careers;
use App\Models\Resources;
use App\Models\Phases;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiServices
{
    public function generateCareer(string $title)
    {
        $slug = Str::slug($title);

        $existing = Careers::where('slug', $slug)->first();

        if ($existing) {
            return $existing;
        }

        $aiResponse = $this->callAI($title);

        return $this->save($aiResponse, 1);
    }

    /**
     * Call Gemini 2.5 Flash
     */
    private function callAI(string $title): array
    {
        $response = Http::timeout(120)
            ->acceptJson()
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' .
                    config('services.gemini.api_key'),
                [
                    "contents" => [
                        [
                            "parts" => [
                                [
                                    "text" => $this->buildPrompt($title)
                                ]
                            ]
                        ]
                    ],

                    "generationConfig" => [
                        "temperature" => 0.4,
                        "topP" => 0.95,
                        "topK" => 40,
                        "maxOutputTokens" => 4096,
                        "responseMimeType" => "application/json"
                    ]
                ]
            );

        if ($response->failed()) {

            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            dd($response);
            throw new \Exception('API did not pick up your call ');
        }

        $json = $response->json();
        //dd($json);

        Log::info('Gemini Response', $json);

        // Extract generated text
        $text = data_get(
            $json,
            'candidates.0.content.parts.0.text',
            ''
        );

        if (empty($text)) {
            Log::error('Gemini returned empty response', [
                'response' => $json
            ]);

            throw new \Exception('Gemini returned an empty response.');
        }

        // Remove markdown if model accidentally returns it
        $text = preg_replace('/^```json\s*/', '', $text);
        $text = preg_replace('/```$/', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {

            Log::error('Invalid JSON returned by Gemini', [
                'json_error' => json_last_error_msg(),
                'raw' => $text
            ]);

            throw new \Exception('Gemini returned invalid JSON.');
        }

        return $data;
    }

    /**
     * Save AI response
     */
    private function save(array $aiResponse, int $userId): Careers
    {
        return DB::transaction(function () use ($aiResponse, $userId) {

            $career = Careers::create([
                'user_id'       => 1, /* Auth::id() should be replace in controller implementation*/
                'slug'          => Str::slug($aiResponse['title']),
                'title'         => $aiResponse['title'],
                'description'   => $aiResponse['description'],
                'category'      => $aiResponse['category'],
                'salary_range'  => $aiResponse['salary']['range'] ?? 'Not specified',
                'salary_period' => $aiResponse['salary']['period'] ?? 'annual',
                'duration'      => $aiResponse['duration']['label'] ?? 'Not specified',
                'skills'        => json_encode($aiResponse['skills'] ?? []),
                'demand'        => $aiResponse['demand'] ?? 'medium',
                'reviewed_by'   => 'AI Generated',
                'is_published'  => true,
            ]);

            foreach ($aiResponse['phases'] as $phaseData) {

                $phase = Phases::create([
                    'career_id'      => $career->id,
                    'sequence_num'   => $phaseData['order'],
                    'title'          => $phaseData['name'],
                    'description'    => $phaseData['description'],
                    'duration_ranges' => $phaseData['duration_range'] ?? 'Not specified',
                    'skills'         => json_encode($phaseData['skills'] ?? []),
                ]);

                foreach ($phaseData['resources'] as $resourceData) {

                    Resources::create([
                        'phase_id'   => $phase->id,
                        'title'      => $resourceData['title'],
                        'url'        => $resourceData['url'],
                        'type'       => $resourceData['type'],
                        'badge'      => $resourceData['badge'] ?? 'free',
                        'difficulty' => $resourceData['difficulty'] ?? 'medium',
                    ]);
                }
            }

            return $career->load('phases.resources');
        });
    }

    /**
     * Prompt
     */
    private function buildPrompt(string $careerTitle): string
    {
        return <<<PROMPT
Generate a complete career roadmap for "{$careerTitle}".

Return ONLY valid JSON.



Use exactly this schema:

{
  "title": "",
  "description": "",
  "category": "",
  "demand": "(Low , Medium , High)",
  "duration": {
    "label": ""
  },
  "salary": {
    "range": "",
    "period": "annual"
  },
  "skills": [],
  "phases": [
    {
      "order": 1,
      "name": "",
      "description": "",
      "duration_range": "",
      "skills": [],
      "resources": [
        {
          "title": "",
          "url": "",
          "type": "course"
        }
      ]
    }
  ]
}

Requirements:

- 4–6 phases.
- Each phase should have 2–4 real learning resources.
- Use real URLs.
- Include duration_range.
- Include skills in every phase.
- Return ONLY JSON.
PROMPT;
    }
}
