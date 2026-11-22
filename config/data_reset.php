<?php

/*
|--------------------------------------------------------------------------
| Erase all data (Super Admin → Erase All Data)
|--------------------------------------------------------------------------
|
| The password that unlocks the "erase all business data" button. While it
| is empty (or shorter than 8 characters) the feature is switched off and
| the button can never be used. Set it in the server's .env only.
|
*/

return [
    'password' => env('DATA_RESET_PASSWORD'),

    // How long one correct password keeps the erase button unlocked.
    'unlock_minutes' => 10,

    // The exact phrase that must be typed to confirm.
    'confirmation_phrase' => 'ERASE ALL DATA',
];
