<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email messages
    |--------------------------------------------------------------------------
    |
    */

    'server' => 'server|servers',
    'certificate' => 'certificate|certificates',

    'expiry7' => 'There is 1 certificate expiring in the next 7 days:'
               .'|There are :certificates expiring in the next 7 days:',
    'expiry7empty' => 'There are no certificates requiring urgent action within next 7 days.',

    'expiry28' => 'Another :certificates certificate will expire within the next 28 days:'
                .'|Another :certificates certificates will expire within the next 28 days:',
    'expiry28empty' => 'There are no certificates requiring planning of their renewal in the next 28 days.',

    'Notes' => 'A note|Notes',

];
