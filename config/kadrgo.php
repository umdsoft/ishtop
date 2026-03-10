<?php

return [
    'name' => 'KadrGo',
    'version' => '4.0',

    // Narxlar (so'mda)
    'pricing' => [
        'vacancy' => 35_000,
        'top' => 15_000,
        'urgent' => 10_000,
        'resume_contact' => 7_000,
        'extend' => 15_000,
        'candidate_unlock' => 25_000,
    ],

    // E'lon muddatlari (kunlarda)
    'durations' => [
        'vacancy' => 15,
        'top' => 7,
        'urgent' => 3,
    ],

    // Rate limitlar
    'rate_limits' => [
        'api' => 60,           // per min per user
        'auth_otp' => 5,       // per min per IP
        'vacancy_create' => 10, // per hour per user
        'application_free' => 30, // per day
        'chat_message' => 60,  // per min per user
        'recruiter_api' => 120, // per min per user
        'questionnaire_submit' => 10, // per min per user
        'banner_impression' => 100, // per min per user
    ],

    // Scoring
    'scoring' => [
        'green_threshold' => 80,
        'yellow_threshold' => 50,
        'suspicious_time_seconds' => 10,
    ],

    // Anti-fraud
    'anti_fraud' => [
        'max_banner_clicks_per_day' => 3,
        'duplicate_text_threshold' => 85, // %
        'reports_for_moderation' => 3,
        'reports_for_auto_ban' => 5,
    ],
];
