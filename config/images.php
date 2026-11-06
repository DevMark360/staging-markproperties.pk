<?php

/*
| Uploaded images (admin panel). Every upload is converted to WebP and saved at these widths;
| the original file is not kept. Smaller images are never enlarged.
*/

return [
    'disk' => env('IMAGES_DISK', 'site'), // public/ — uploads land in public/uploads/<folder>

    'sizes' => [
        'card' => 800,   // cards, listings, thumbnails
        'large' => 1600, // full-screen gallery
        'hero' => 2400,  // home page slider photos only (full-screen on large and high-density screens)
    ],

    // Sizes made for an upload unless the caller asks for others
    'default_sizes' => ['card', 'large'],

    // Home slider photos narrower than this look soft once stretched across a desktop screen
    'hero_min_width' => 1920,

    'quality' => 80, // WebP quality, 0–100

    // Refuse images larger than this many pixels (width × height) before decoding: 25 MP covers any phone or camera
    'max_pixels' => 25_000_000,

    // PHP memory limit while an image is being resized (restored straight after)
    'memory_limit' => '512M',
];
