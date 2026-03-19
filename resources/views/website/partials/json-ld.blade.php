@php
    $employmentType = match($vacancy->work_type?->value) {
        'full_time' => 'FULL_TIME',
        'part_time' => 'PART_TIME',
        'temporary' => 'TEMPORARY',
        'remote' => 'FULL_TIME',
        default => 'OTHER',
    };

    $jsonLd = [
        '@context' => 'https://schema.org/',
        '@type' => 'JobPosting',
        'title' => $vacancy->title($lang),
        'description' => Str::limit(strip_tags($vacancy->description($lang)), 5000),
        'identifier' => [
            '@type' => 'PropertyValue',
            'name' => 'KadrGo',
            'value' => $vacancy->id,
        ],
        'datePosted' => $vacancy->published_at?->toIso8601String(),
        'employmentType' => $employmentType,
        'directApply' => true,
        'hiringOrganization' => array_filter([
            '@type' => 'Organization',
            'name' => $vacancy->company_name,
            'logo' => $vacancy->employer?->logo_url,
        ]),
        'jobLocation' => [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $vacancy->district ?? $vacancy->city ?? 'Toshkent',
                'addressCountry' => 'UZ',
            ],
        ],
    ];

    if ($vacancy->expires_at) {
        $jsonLd['validThrough'] = $vacancy->expires_at->toIso8601String();
    }

    if ($vacancy->salary_min || $vacancy->salary_max) {
        $salaryValue = ['@type' => 'QuantitativeValue', 'unitText' => 'MONTH'];
        if ($vacancy->salary_min && $vacancy->salary_max) {
            $salaryValue['minValue'] = $vacancy->salary_min;
            $salaryValue['maxValue'] = $vacancy->salary_max;
        } elseif ($vacancy->salary_min) {
            $salaryValue['value'] = $vacancy->salary_min;
        } else {
            $salaryValue['value'] = $vacancy->salary_max;
        }
        $jsonLd['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => 'UZS',
            'value' => $salaryValue,
        ];
    }

    // Qualifications (requirements)
    $requirements = $vacancy->requirements($lang);
    if ($requirements) {
        $jsonLd['qualifications'] = Str::limit(strip_tags($requirements), 2000);
    }

    // Responsibilities
    $responsibilities = $vacancy->responsibilities($lang);
    if ($responsibilities) {
        $jsonLd['responsibilities'] = Str::limit(strip_tags($responsibilities), 2000);
    }
@endphp
<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
