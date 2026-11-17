<?php

// Only the settings that differ from Livewire's own defaults (vendor/livewire/livewire/config/livewire.php).

return [

    // Admin uploads: Livewire caps every upload at 12 MB unless told otherwise. That cap is lifted here, so each
    // upload field sets its own limit with ->maxSize() (area photos have none). The server's PHP settings
    // upload_max_filesize and post_max_size still apply on top.
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK'),
        'rules' => ['required', 'file'],
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 15, // minutes: big photos on a slow connection need longer than the default 5
        'cleanup' => true,
    ],

];
