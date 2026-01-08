<?php

return [
    // Dit zorgt ervoor dat config('jwt.secret') de waarde uit je .env leest
    'secret' => env('JWT_SECRET'),
];