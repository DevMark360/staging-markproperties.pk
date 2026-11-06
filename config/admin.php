<?php

/*
| Admin panel (Filament). The path is kept out of the code and is hard to guess on purpose;
| set ADMIN_PATH in .env (local) and on the server.
*/

return [
    // `?:` also covers a blank ADMIN_PATH= line (env() returns "" for it, not the default), which would
    // otherwise put the admin panel at the site root (D1)
    'path' => trim((string) env('ADMIN_PATH'), '/') ?: 'mp-office',
];
