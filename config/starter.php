<?php

return [
    'site_url' => env('SITE_URL', env('APP_URL', 'http://localhost')),
    'preview' => (bool) env('STARTER_PREVIEW', false),
    'exporting' => false,
    'showcase' => (bool) env('STARTER_SHOWCASE', false),
    // Retain legacy records for lead references, without exposing them as public pages.
    'retired_paths' => ['/booking'],
    'retired_templates' => ['salon-booking'],
    'templates' => ['sections' => 'pages.sections', 'studio' => 'pages.studio',
        'salon-home' => 'salon.pages.home', 'salon-about' => 'salon.pages.about',
        'salon-services' => 'salon.pages.services', 'salon-gallery' => 'salon.pages.gallery',
        'salon-contact' => 'salon.pages.contact', 'salon-booking' => 'salon.pages.booking'],
    'media' => ['disk' => 'public', 'max_kb' => 8192, 'max_pixels' => 25000000, 'widths' => [640, 1280, 1920], 'queue' => (bool) env('MEDIA_QUEUE', false)],
    'leads' => [
        'notification_email' => env('LEAD_NOTIFICATION_EMAIL'),
        'webhook_url' => env('LEAD_WEBHOOK_URL'),
        'retention_days' => (int) env('LEAD_RETENTION_DAYS', 180),
        'consent' => (bool) env('LEAD_CONSENT_REQUIRED', true),
        'captcha_secret' => env('TURNSTILE_SECRET_KEY'),
        'captcha_site_key' => env('TURNSTILE_SITE_KEY'),
        // Add client fields here; only these keys are accepted and stored.
        'extra_fields' => [],
    ],
];
