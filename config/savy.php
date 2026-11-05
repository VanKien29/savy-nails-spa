<?php

// Defaults match the approved Client. Saved CMS values take precedence.
return [
    'images' => [
        'savy-hero' => ['savy/26867-e1713761727547.jpg', 'Nude manicure with delicate dried flowers'],
        'savy-circle' => ['savy/line-circle-2.png', 'Botanical circle decoration'],
        'savy-about-scene' => ['savy/16933.jpg', 'About section background'],
        'savy-menu-background' => ['savy/1007.jpg', 'Services background'],
        'savy-feature' => ['savy/22224199.jpg', 'Nail care inspiration'],
        'savy-feature-background' => ['savy/leaf-png.png', 'Botanical feature background'],
        'savy-gallery-background' => ['savy/5172.jpg', 'Gallery background'],
        'savy-contact-background' => ['savy/55792.jpg', 'Contact background'],
        'savy-footer-background' => ['savy/1244.jpg', 'Footer background'],
        'savy-about-banner' => ['savy/banner-about.png', 'Warm bronze spa treatment'],
        'savy-services-banner' => ['savy/banner-services.png', 'Nude and gold manicure'],
        'savy-gallery-banner' => ['savy/banner-gallery.png', 'Gold manicure inspiration'],
        'savy-contact-banner' => ['savy/banner-about.png', 'Warm bronze spa treatment'],
        'savy-strength-1' => ['savy/5.jpg', 'Nail and spa treatment inspiration'],
        'savy-strength-2' => ['savy/1.jpg', 'Nail and spa treatment inspiration'],
        'savy-strength-3' => ['savy/3.jpg', 'Nail and spa treatment inspiration'],
        'savy-strength-4' => ['savy/6.jpg', 'Nail and spa treatment inspiration'],
        ...collect(range(1, 12))->mapWithKeys(fn ($number) => ['savy-gallery-'.$number => ['savy/avatar-'.$number.'.jpg', 'Nail design inspiration '.$number]])->all(),
    ],
    'profile' => [
        'images' => [
            'hero' => 'savy-hero', 'circle' => 'savy-circle', 'hero_logo' => 'savy-logo-art',
            'intro' => 'savy-about-scene', 'menu' => 'savy-menu-background',
            'feature' => 'savy-feature', 'feature_background' => 'savy-feature-background',
            'home_gallery_background' => 'savy-gallery-background', 'contact_background' => 'savy-contact-background',
            'footer_background' => 'savy-footer-background',
            'about_banner' => 'savy-about-banner', 'services_banner' => 'savy-services-banner',
            'gallery_banner' => 'savy-gallery-banner', 'contact_banner' => 'savy-contact-banner',
        ],
        'home_gallery' => ['savy-gallery-8', 'savy-gallery-2', 'savy-gallery-1', 'savy-gallery-4', 'savy-gallery-12', 'savy-gallery-7'],
        'gallery' => array_map(fn ($number) => 'savy-gallery-'.$number, range(1, 12)),
        'footer' => ['contact_label' => 'Contact', 'hours_label' => 'Business Hours', 'map_label' => 'Open in Google Maps ↗'],
    ],
    'pages' => [
        'home' => [
            'hero' => ['heading' => 'Nail Salon in Walla Walla, WA 99362'],
            'intro' => [
                'heading' => 'About Us', 'eyebrow' => 'Welcome to Savy Nails & Spa, where beauty meets relaxation!',
                'body' => 'Take a moment for yourself with a manicure, nail enhancement, relaxing spa pedicure or waxing service. Bring your inspiration and enjoy a finish that feels like you.',
                'items' => [['text' => 'Savy Nails & Spa is located at']],
            ],
            'menu' => ['heading' => 'Services', 'button_label' => 'Show More', 'button_url' => '/services'],
            'feature' => [
                'heading' => 'Your Savy Moment', 'body' => "A little self-care.\nA beautiful finish.",
                'items' => [['text' => 'From everyday polish to your next special occasion, explore a menu made for your moment.']],
                'button_label' => 'Call or message Savy to book', 'button_url' => '/contact-us',
            ],
            'gallery' => ['heading' => 'Gallery', 'button_label' => 'Show More', 'button_url' => '/gallery'],
            'faq' => ['heading' => 'FAQ', 'items' => [
                ['title' => 'Where is Savy Nails & Spa?', 'text' => '', 'value' => 'address'],
                ['title' => 'What services do you offer?', 'text' => 'Explore manicures, nail enhancements, spa pedicures, additions and waxing on our Services page.', 'value' => 'custom'],
                ['title' => 'How can I make an appointment?', 'text' => '', 'value' => 'booking'],
                ['title' => 'What are your business hours?', 'text' => '', 'value' => 'hours'],
                ['title' => 'How can I ask about a price?', 'text' => 'Call or message the salon for prices marked $.... and details about services marked +.', 'value' => 'custom'],
            ]],
        ],
        'about' => [
            'banner' => ['heading' => 'About Us'],
            'intro' => ['heading' => 'Welcome to Savy Nails & Spa, where beauty meets relaxation!', 'body' => 'Make time for a little self-care. At Savy Nails & Spa, explore manicures, spa pedicures, nail enhancements and waxing in Walla Walla. Bring your favorite shape, color or inspiration and let us help you choose your next finish.'],
            'strengths' => ['heading' => 'Our Strengths', 'button_label' => 'Contact to Book', 'button_url' => '/contact-us', 'items' => [
                ['label' => 'savy-strength-1', 'title' => 'Beautiful Details', 'text' => 'From a simple polish to your next set, discover the finishing details on our manicure and enhancement menu.'],
                ['label' => 'savy-strength-2', 'title' => 'A Moment for You', 'text' => 'Take a little time to slow down and enjoy your next nail or spa service.'],
                ['label' => 'savy-strength-3', 'title' => 'A Menu for Your Style', 'text' => 'Explore manicures, spa pedicures, additions and waxing, with prices available on our Services page.'],
                ['label' => 'savy-strength-4', 'title' => 'Your Next Visit', 'text' => 'Find us in Walla Walla. Call the salon or message us on Facebook or Instagram to arrange your visit.'],
            ]],
        ],
        'services' => ['banner' => ['heading' => 'Services'], 'menu' => ['heading' => 'Services']],
        'gallery' => ['banner' => ['heading' => 'Gallery'], 'gallery' => ['heading' => 'Nail inspiration']],
        'contact' => [
            'banner' => ['heading' => 'Contact Us'],
            'details' => ['heading' => 'Contact', 'items' => [['title' => 'Business Hours'], ['title' => 'Social Media']]],
            'card' => ['heading' => 'Let’s plan your visit', 'body' => 'For appointments, call Savy Nails & Spa or message us through our social pages.', 'items' => [['title' => 'Call'], ['title' => 'Message on']]],
        ],
    ],
];
