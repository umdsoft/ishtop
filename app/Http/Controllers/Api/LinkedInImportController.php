<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LinkedInPdfParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkedInImportController extends Controller
{
    public function __construct(
        private LinkedInPdfParserService $parserService,
    ) {}

    /**
     * Upload and parse a LinkedIn PDF export.
     * Returns parsed data as a preview before applying.
     */
    public function parsePdf(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $parsedData = $this->parserService->parseFromUpload($request->file('file'));

        if (empty($parsedData)) {
            return response()->json([
                'message' => 'PDF ni tahlil qilib bo\'lmadi. LinkedIn profilingizni PDF formatida yuklab oling.',
            ], 422);
        }

        $mappedData = $this->parserService->mapToWorkerProfile($parsedData);

        $currentProfile = $request->user()->workerProfile;

        return response()->json([
            'parsed' => $parsedData,
            'mapped' => $mappedData,
            'current' => $currentProfile?->only([
                'full_name', 'specialty', 'city', 'education_level',
                'experience_years', 'skills', 'bio', 'linkedin_url',
            ]),
        ]);
    }

    /**
     * Apply parsed LinkedIn data to worker profile.
     */
    public function applyImport(Request $request): JsonResponse
    {
        $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['string', 'in:full_name,specialty,city,education_level,experience_years,skills,bio,linkedin_url'],
            'data' => ['required', 'array'],
            'raw_data' => ['nullable', 'array'],
        ]);

        $profile = $request->user()->workerProfile;
        if (!$profile) {
            return response()->json(['message' => 'Profil topilmadi'], 404);
        }

        $fieldsToUpdate = $request->input('fields');
        $importData = $request->input('data');

        $updatePayload = [];
        foreach ($fieldsToUpdate as $field) {
            if (isset($importData[$field])) {
                $updatePayload[$field] = $importData[$field];
            }
        }

        $updatePayload['linkedin_import_data'] = $request->input('raw_data');
        $updatePayload['linkedin_imported_at'] = now();

        $profile->update($updatePayload);

        return response()->json([
            'message' => 'LinkedIn ma\'lumotlari muvaffaqiyatli import qilindi',
            'profile' => $profile->fresh(),
        ]);
    }
}
