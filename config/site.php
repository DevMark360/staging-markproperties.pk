<?php

return [

    'name' => 'Mark Properties',

    'domain' => 'markproperties.pk',

    'country' => 'Pakistan',

    'primary_city' => 'karachi',

    // Short brand line shown in the footer. Hidden while null.
    'tagline' => null,

    // Where new-lead emails go (D2): enquiries, contact messages, partner requests and launch-alert sign-ups.
    // Empty to switch the emails off. Mail itself is set up with MAIL_* in .env.
    'leads_email' => env('LEADS_EMAIL', env('SITE_EMAIL', 'info@markproperties.pk')),

    // Base map for the project maps (MapLibre style URL). Switch it in .env (MAP_STYLE_URL) if the service is down (S2).
    'map_style' => env('MAP_STYLE_URL', 'https://tiles.openfreemap.org/styles/liberty'),

    // Contact details. Override per environment in .env; a block is hidden when its value is empty.
    'contact' => [
        // No phone or WhatsApp support: visitors leave their details through the forms and the team calls them back.
        // There are no phone or WhatsApp settings any more (R3), so they can't be switched back on from .env.
        'email' => env('SITE_EMAIL', 'info@markproperties.pk'),
        'hours' => env('SITE_HOURS', 'Monday to Saturday, 10:00 am to 7:00 pm'),
        'address' => env('SITE_ADDRESS', 'Plot Number B-354, Ground Floor, C.H.S, Mohammad Ali Society Block 7-8 Adamjee Nagar Society, Kathiawaar, Karachi, Pakistan'),
    ],

    // "Book a meeting" on the Partner page: a Google Calendar appointment schedule (Google Meet) or Cal.com link (cal.com/<user>/<event>),
    // shown in the page. Set BOOKING_URL in .env; the section is hidden while it is empty.
    'booking_url' => env('BOOKING_URL'),

    // Office location (Google Maps embed + a link that opens the place in Google Maps)
    'map' => [
        'embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3619.4821657873595!2d67.0840087!3d24.8815298!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33f1900ffca1b%3A0x3576e71c1b40db05!2sMark%20Properties!5e0!3m2!1sen!2s!4v1793607230289!5m2!1sen!2s',
        'link' => 'https://www.google.com/maps/search/?api=1&query=24.8815298,67.0840087',
    ],

    'social' => [
        'Facebook' => 'https://www.facebook.com/markpropertiespk/',
        'Instagram' => 'https://instagram.com/markproperties.pk',
        'LinkedIn' => 'https://pk.linkedin.com/company/markpropertiespk',
        'YouTube' => 'https://youtube.com/channel/UCIURvxsMpDyf3X9W0BHauFg',
    ],

    /*
    | Primary navigation — off-plan focus only. Never add Rent, Sell, Agents, Careers,
    | regional stats, payment plans, unit details or comparison tools.
    |
    | An item with `columns` opens a full-width dropdown panel. `feature` names a project slug
    | (config/projects.php) shown as a card in the panel; without it an advisor card is shown.
    | `flatten` items are listed by their links in the footer instead of by their own label.
    | `stacked` items show their groups one above the other in the desktop panel, links in 3 columns
    | (for groups of very different lengths, so the panel has no empty corner).
    */
    'nav' => [
        [
            'label' => 'New Projects',
            'route' => 'projects.index',
            'columns' => [
                // Stage and city links are added from the admin's Stages and Cities (App\Support\Nav)
                ['heading' => 'By stage', 'links' => [
                    ['label' => 'All New Projects', 'route' => 'projects.index'],
                ]],
                ['heading' => 'By city', 'links' => []],
            ],
            'stacked' => true,
            'feature' => 'falaknaz-skyview-towers',
        ],
        [
            'label' => 'Buy',
            'route' => 'projects.index',
            'columns' => [
                ['heading' => 'Residential', 'links' => [
                    ['label' => 'Apartments', 'route' => 'projects.index', 'params' => ['type' => 'apartments']],
                    ['label' => 'Houses & Villas', 'route' => 'projects.index', 'params' => ['type' => 'houses']],
                    ['label' => 'Plots', 'route' => 'projects.index', 'params' => ['type' => 'plots']],
                ]],
                ['heading' => 'Commercial', 'links' => [
                    ['label' => 'Shops & Showrooms', 'route' => 'projects.index', 'params' => ['type' => 'shops']],
                    ['label' => 'Offices', 'route' => 'projects.index', 'params' => ['type' => 'offices']],
                    ['label' => 'Mixed-Use Projects', 'route' => 'projects.index', 'params' => ['type' => 'mixed-use']],
                ]],
            ],
            'feature' => 'bloom-gardens',
        ],
        [
            'label' => 'Luxury',
            'route' => 'luxe',
            'columns' => [
                ['heading' => 'Luxury collection', 'links' => [
                    ['label' => 'All Luxury Projects', 'route' => 'luxe'],
                    ['label' => 'Waterfront Living', 'route' => 'luxe', 'params' => ['collection' => 'waterfront']],
                    ['label' => 'Signature High-Rises', 'route' => 'luxe', 'params' => ['collection' => 'high-rise']],
                    ['label' => 'Luxury Villas', 'route' => 'luxe', 'params' => ['collection' => 'villas']],
                ]],
            ],
            'feature' => 'saima-mangrove-residences',
        ],
        // A top-level link (no dropdown), so builders find it without opening More
        [
            'label' => 'Partner With Us',
            'route' => 'partner',
        ],
        [
            'label' => 'More',
            'route' => 'about',
            'flatten' => true,
            'columns' => [
                ['heading' => 'Company', 'links' => [
                    ['label' => 'About Us', 'route' => 'about'],
                    ['label' => 'Developers', 'route' => 'developers.index'],
                    ['label' => 'Contact Us', 'route' => 'contact'],
                ]],
                ['heading' => 'Resources', 'links' => [
                    ['label' => 'Blog', 'route' => 'blog'],
                    ['label' => 'FAQs', 'route' => 'faqs'],
                    ['label' => "Buyer's Guide", 'route' => 'guide'],
                    // News & Media returns once there is real press coverage to show
                ]],
            ],
        ],
    ],

];
