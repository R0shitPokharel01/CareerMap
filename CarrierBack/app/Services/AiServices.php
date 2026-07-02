<?php

namespace App\Services;

use App\Models\Careers;
use App\Models\Resources;
use App\Models\Phases;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

    public function callAI(string $title)
    {
        $response = Http::timeout(60)->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . config('services.gemini.api_key'),
            [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $this->buildPrompt($title)
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 4000,
                    'responseMimeType' => 'application/json',
                ],
            ]
        );
        if ($response->failed()) {
            Log::error(' API call failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('AI service unavailable. Please try again.');
        }

        // Extract the text block from Claude's response
        $text = $response->json()['content'][0]['text'] ?? '';

        // Strip markdown fences if Claude wraps the JSON in ```json ... ```
        $text = preg_replace('/```json\s*|```/', '', $text);
        $text = trim($text);

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            Log::error('Claude returned invalid JSON', ['raw' => $text]);
            throw new \Exception('AI returned invalid data. Please try again.');
        }

        return $data;
    }

    private function save(array $aiResponse, int $UserId): Careers
    {
        return DB::transaction(function () use ($aiResponse, $UserId) {

            $career = Careers::create([
                'user_id'      => $UserId,
                'slug'         => Str::slug($aiResponse['title']),
                'title'        => $aiResponse['title'],
                'description'  => $aiResponse['description'],
                'category'     => $aiResponse['category'],
                'salary_range' => $aiResponse['salary']['range']  ?? 'Not specified',
                'salary_period' => $aiResponse['salary']['period'] ?? 'annual',
                'duration'     => $aiResponse['duration']['label'] ?? 'Not specified',
                'skills'       => json_encode($aiResponse['skills'] ?? []),
                'demand'       => $aiResponse['demand'] ?? 'medium',
                'reviewed_by'  => 'AI Generated',
                //'is_published' => true,
            ]);

            foreach ($aiResponse['phases'] as $phaseData) {

                $phase = Phases::create([
                    'career_id'      => $career->id,
                    'sequence_num'   => $phaseData['order'],
                    'title'          => $phaseData['name'],
                    'description'    => $phaseData['description'],
                    'duration_range' => $phaseData['duration_range'] ?? 'Not specified',
                    'skills'         => json_encode($phaseData['skills'] ?? []),
                ]);


                foreach ($phaseData['resources'] as $resourceData) {

                    Resources::create([
                        'phase_id'   => $phase->id,
                        'title'      => $resourceData['title'],
                        'url'        => $resourceData['url'],
                        'type'       => $resourceData['type'],
                        'badge'      => $resourceData['badge']      ?? 'free',
                        'difficulty' => $resourceData['difficulty'] ?? 'medium',
                    ]);
                }
            }

            return $career->load('phases.resources');
        });
    }

    // for AI prompt
    private function buildPrompt(string $careerTitle)
    {
        return <<<PROMPT
            Generate a career roadmap for : "{$careerTitle}".

        Return ONLY valid JSON — no explanation, no markdown. Use this exact structure:

        {
          "title": "Career name",
          "description": "2-3 sentence overview",
          "category": "Development | Design | Security | Data | Management | Other",
          "demand": "low | medium | high",
          "duration": { "label": "e.g. 12–18 months" },
          "salary": { "range": "e.g. $60,000 – $120,000", "period": "annual" },
          "skills": ["skill1", "skill2", "skill3"],
          "phases": [
            {
              "order": 1,
              "name": "Phase title",
              "description": "What the learner achieves",
              "resources": [
                {
                  "title": "Resource name",
                  "url": "https://real-url.com",
                  "type": "article | video | course | book | platform"
                }
              ]
            }
          ]
        }

        Rules: 4-6 phases. 2-4 resources per phase. Real URLs. Return ONLY JSON.
        PROMPT;
    }
}
