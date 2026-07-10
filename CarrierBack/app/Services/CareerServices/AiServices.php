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
You are a senior career-planning expert and curriculum designer with deep, current knowledge of the "{$careerTitle}" field — including realistic entry paths, the tools/technologies actually used in the field today, common certifications, and reputable learning platforms (Coursera, Udemy, edX, freeCodeCamp, official docs, YouTube channels, industry-specific bodies, etc.).

TASK
Generate a complete, realistic, non-generic career roadmap for becoming a "{$careerTitle}".

OUTPUT FORMAT
Return ONLY a single valid JSON object. No markdown formatting, no code fences, no commentary before or after, no trailing commas, no comments inside the JSON.

SCHEMA
{
  "title": "string — properly capitalized career title",
  "description": "string — 2-3 sentences: what this role does day-to-day and who it suits",
  "category": "string — e.g. Technology, Healthcare, Design, Business, Trades, Creative",
  "demand": "Low | Medium | High",
  "demand_reason": "string — 1 sentence, cite a concrete factor (industry growth rate, automation exposure, hiring volume, etc.)",
  "prerequisites": ["array of 0-4 realistic prerequisites, e.g. 'Bachelor's degree in related field', 'Basic algebra', or empty array if none"],
  "duration": {
    "label": "string — total realistic time to job-ready, e.g. '6-12 months'"
  },
  "salary": {
    "range": "string — realistic entry-to-mid salary range in USD, e.g. '$55,000 - $85,000'",
    "period": "annual",
    "note": "string — 1 short clause on what drives variation, e.g. 'varies significantly by region and company size'"
  },
  "skills": ["6-10 core skills for the whole career, ordered by importance"],
  "tools": ["3-8 specific tools/software/technologies a working professional actually uses"],
  "certifications": ["0-4 real, named certifications worth pursuing, or empty array if the field doesn't use them"],
  "career_paths": ["2-4 realistic next-step roles after 3-5 years, e.g. 'Senior X', 'X Team Lead', 'Freelance X'"],
  "phases": [
    {
      "order": 1,
      "name": "string — clear phase name",
      "level": "beginner | intermediate | advanced",
      "description": "string — 1-2 sentences on what's accomplished and why it matters",
      "duration_range": "string — e.g. '4-6 weeks'",
      "skills": ["2-5 specific, actionable skills taught in this phase"],
      "milestone": "string — a concrete, verifiable output proving this phase is done, e.g. 'Deploy a working REST API' or 'Pass the CompTIA A+ exam'",
      "resources": [
        {
          "title": "string — real name of a specific course, book, cert, or tool",
          "provider": "string — the real organization/platform/publisher, e.g. 'Coursera', 'O'Reilly', 'freeCodeCamp'",
          "url": "string — real, currently active URL to that specific resource",
          "type": "course | book | certification | tool | documentation | video | project",
          "cost": "free | paid | freemium"
        }
      ]
    }
  ]
}

CONTENT RULES
- phases, ordered logically from beginner to job-ready in detail.
- Each phase has 2-4 resources from real, well-known, field-relevant platforms — never generic filler like "Online Course Platform" or "YouTube tutorial."
- No duplicate resources across phases.
- Mix resource types and costs — don't make every resource a paid course.
- Every phase needs a "milestone" that is concrete and checkable, not vague ("understand basics" is not acceptable; "build and deploy a portfolio site with 3 projects" is).
- Skills must be actionable, not abstract: "Write SQL JOIN and subquery statements" not "Learn databases."
- Do not reuse boilerplate phrasing across different careers — tailor description, tools, and resources specifically to "{$careerTitle}".
- If you are not confident a specific URL is real and correct, use the provider's known official domain (e.g. their course catalog or homepage) rather than inventing a deep link.

VALIDATION
Before returning, verify: valid JSON syntax, all required fields present, phases array has 4-6 items, each phase has at least 2 resources, no duplicate resource titles.

Return ONLY the JSON object.
PROMPT;
    }
}