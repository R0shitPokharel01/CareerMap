<?php

namespace App\Services\CareerServices;

use App\Models\Careers;
use App\Models\Resources;
use App\Models\Phases;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AiServices
{
    public function generateCareer(string $title, int $userId): Careers
    {
        try {
            $slug = Str::slug($title);

            $existing = Careers::where('slug', $slug)->first();

            if ($existing) {
                return $existing;
            }

            $aiResponse = $this->callAI($title);

            return $this->save($aiResponse, $userId);
        } catch (\Throwable $e) {

            Log::error('Career generation failed', [
                'title' => $title,
                'message' => $e->getMessage(),
            ]);

            throw new \Exception('Unable to generate career roadmap.');
        }
    }


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
                                ["text" => $this->buildPrompt($title)]
                            ]
                        ]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.4,
                        "topP" => 0.95,
                        "topK" => 40,
                        "maxOutputTokens" => 8192,
                        "responseMimeType" => "application/json"
                    ]
                ]
            );


        if ($response->status() === 429) {
            Log::error('Gemini API rate limit exceeded', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            dd('rate limit exceeds');
            throw new \Exception('AI service is temporarily busy due to rate limits. Please try again in a few minutes.');
        }

        if ($response->failed()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception("Gemini API failed with status {$response->status()}");
        }

        $json = $response->json();
        Log::info('Gemini Response', $json ?? []);

        if (
            data_get($json, 'candidates.0.finishReason') === 'MAX_TOKENS'
        ) {
            throw new \Exception(
                'Gemini response was truncated. Increase maxOutputTokens or shorten the prompt.'
            );
        }

        $text = data_get($json, 'candidates.0.content.parts.0.text', '');

        if (empty($text)) {
            Log::error('Gemini returned empty response', ['response' => $json]);
            throw new \Exception('Gemini returned an empty response.');
        }

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

        if (empty($data['title']) || empty($data['phases']) || !is_array($data['phases'])) {
            Log::error('Gemini JSON missing required fields', ['data' => $data]);
            throw new \Exception('Gemini returned an incomplete career roadmap.');
        }

        return $data;
    }

    /**
     * Save AI response
     */
    private function save(array $aiResponse, int $userId): Careers
    {
        try {
            return DB::transaction(function () use ($aiResponse, $userId) {

                $career = Careers::create([
                    'user_id'       => $userId,
                    'slug'          => Str::slug($aiResponse['title']),
                    'title'         => $aiResponse['title'],
                    'description'   => $aiResponse['description'] ?? null,
                    'category'      => $aiResponse['category'] ?? null,
                    'salary_range'  => $aiResponse['salary']['range'] ?? 'Not specified',
                    'salary_period' => $aiResponse['salary']['period'] ?? 'annual',
                    'duration'      => $aiResponse['duration']['label'] ?? 'Not specified',
                    'skills'        => $aiResponse['skills'] ?? [],
                    'demand'        => $aiResponse['demand'] ?? 'Medium',

                    'demand_reason' => $aiResponse['demand_reason'] ?? null,
                    'prerequisites' => $aiResponse['prerequisites'] ?? [],
                    'salary_note'   => $aiResponse['salary']['note'] ?? null,
                    'tools'         => $aiResponse['tools'] ?? [],
                    'certifications' => $aiResponse['certifications'] ?? [],
                    'career_paths'  => $aiResponse['career_paths'] ?? [],

                    'reviewed_by'   => 'AI Generated',
                    'is_published'  => true,
                ]);

                foreach (($aiResponse['phases'] ?? []) as $phaseData) {

                    if (empty($phaseData['name'])) {
                        continue;
                    }

                    $phase = Phases::create([
                        'career_id'      => $career->id,
                        'sequence_num'   => $phaseData['order'] ?? null,
                        'title'          => $phaseData['name'],
                        'description'    => $phaseData['description'] ?? null,
                        'duration_range' => $phaseData['duration_range'] ?? 'Not specified',
                        'skills'         => $phaseData['skills'] ?? [],

                        'level'          => $phaseData['level'] ?? null,
                        'milestone'      => $phaseData['milestone'] ?? null,
                    ]);

                    foreach (($phaseData['resources'] ?? []) as $resourceData) {

                        if (empty($resourceData['title']) || empty($resourceData['url']) || empty($resourceData['type'])) {
                            continue;
                        }

                        Resources::create([
                            'phase_id'  => $phase->id,
                            'title'     => $resourceData['title'],
                            'provider'  => $resourceData['provider'] ?? null,
                            'url'       => $resourceData['url'],
                            'type'      => $resourceData['type'],
                            'cost'      => $resourceData['cost'] ?? 'free',
                        ]);
                    }
                }

                return $career->load('phases.resources');
            });
        } catch (\Throwable $th) {
            Log::error('Failed to save generated career', [
                'message' => $th->getMessage(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
            ]);

            throw new \Exception('Failed to save generated career.');
        }
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
- 4-6 phases for each career
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
