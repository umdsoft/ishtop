<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class LinkedInPdfParserService
{
    private Parser $pdfParser;

    public function __construct()
    {
        $this->pdfParser = new Parser();
    }

    /**
     * Parse a LinkedIn PDF from storage.
     */
    public function parse(string $filePath, string $disk = 'public'): array
    {
        try {
            $fullPath = Storage::disk($disk)->path($filePath);
            $pdf = $this->pdfParser->parseFile($fullPath);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                Log::warning('LinkedIn PDF is empty', ['path' => $filePath]);
                return [];
            }

            return $this->extractWithAi(mb_substr($text, 0, 8000));
        } catch (\Exception $e) {
            Log::error('LinkedIn PDF parsing error: ' . $e->getMessage(), ['path' => $filePath]);
            return [];
        }
    }

    /**
     * Parse from an uploaded file directly.
     */
    public function parseFromUpload(UploadedFile $file): array
    {
        try {
            $pdf = $this->pdfParser->parseFile($file->getRealPath());
            $text = $pdf->getText();

            if (empty(trim($text))) {
                return [];
            }

            return $this->extractWithAi(mb_substr($text, 0, 8000));
        } catch (\Exception $e) {
            Log::error('LinkedIn PDF upload parsing error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Use Claude AI to extract structured data from LinkedIn PDF text.
     */
    private function extractWithAi(string $pdfText): array
    {
        $prompt = <<<PROMPT
You are a data extraction specialist. Below is text extracted from a LinkedIn profile PDF export.
Extract the following structured data and return ONLY valid JSON (no markdown, no explanation):

{
    "full_name": "Full name of the person",
    "headline": "Professional headline/specialty",
    "location": "City or location mentioned",
    "education_level": "one of: secondary, vocational, bachelor, master, phd",
    "specialty": "Main profession/role (short, 2-4 words)",
    "experience_years": numeric total years of experience (calculate from dates),
    "skills": ["skill1", "skill2", ...] (max 15 skills),
    "bio": "Summary/about section text (max 300 chars)",
    "work_experience": [
        {
            "company": "Company name",
            "position": "Job title",
            "start_date": "YYYY-MM or YYYY",
            "end_date": "YYYY-MM or present",
            "description": "Brief description (max 200 chars)"
        }
    ],
    "education": [
        {
            "institution": "University/School name",
            "degree": "Degree name",
            "field": "Field of study",
            "year": "Graduation year"
        }
    ],
    "languages": ["language1", "language2"],
    "linkedin_url": "LinkedIn profile URL if found in text"
}

Rules:
- For experience_years: calculate total from work experience dates
- For education_level: map Bachelor->bachelor, Master->master, PhD/Doctorate->phd, college/technical->vocational, high school->secondary
- For skills: extract from Skills section and skills mentioned in experience
- Return ONLY the JSON object, nothing else
- If a field cannot be determined, use null

LinkedIn PDF text:
---
{$pdfText}
---
PROMPT;

        $response = Http::withHeaders([
            'x-api-key' => config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 3000,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if (!$response->successful()) {
            Log::error('Claude API error for LinkedIn PDF parsing', [
                'status' => $response->status(),
            ]);
            return [];
        }

        $content = $response->json('content.0.text', '{}');

        if (preg_match('/\{[\s\S]*\}/m', $content, $matches)) {
            $content = $matches[0];
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            Log::warning('Failed to parse Claude response as JSON');
            return [];
        }

        return $parsed;
    }

    /**
     * Map parsed LinkedIn data to WorkerProfile fields.
     */
    public function mapToWorkerProfile(array $parsedData): array
    {
        $mapped = [];

        if (!empty($parsedData['full_name'])) {
            $mapped['full_name'] = $parsedData['full_name'];
        }

        if (!empty($parsedData['specialty'])) {
            $mapped['specialty'] = mb_substr($parsedData['specialty'], 0, 200);
        } elseif (!empty($parsedData['headline'])) {
            $mapped['specialty'] = mb_substr($parsedData['headline'], 0, 200);
        }

        if (!empty($parsedData['location'])) {
            $mapped['city'] = $parsedData['location'];
        }

        if (!empty($parsedData['education_level'])) {
            $validLevels = ['secondary', 'vocational', 'bachelor', 'master', 'phd'];
            if (in_array($parsedData['education_level'], $validLevels)) {
                $mapped['education_level'] = $parsedData['education_level'];
            }
        }

        if (isset($parsedData['experience_years']) && is_numeric($parsedData['experience_years'])) {
            $mapped['experience_years'] = (int) $parsedData['experience_years'];
        }

        if (!empty($parsedData['skills']) && is_array($parsedData['skills'])) {
            $mapped['skills'] = array_slice($parsedData['skills'], 0, 20);
        }

        if (!empty($parsedData['bio'])) {
            $mapped['bio'] = mb_substr($parsedData['bio'], 0, 2000);
        }

        if (!empty($parsedData['linkedin_url'])) {
            $mapped['linkedin_url'] = $parsedData['linkedin_url'];
        }

        return $mapped;
    }
}
