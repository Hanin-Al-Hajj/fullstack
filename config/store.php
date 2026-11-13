<?php

// Store details shown on the Contact page and available to every page.
// Change them here, or set the STORE_* values in .env.
return [
    'name' => env('STORE_NAME', 'MOUSSAWI'),
    'whatsapp' => env('STORE_WHATSAPP', '96170123456'), // digits only, with country code, no + or spaces
    'phone' => env('STORE_PHONE', '+961 70 123 456'),
    'email' => env('STORE_EMAIL', 'support@example.com'),
    'address' => env('STORE_ADDRESS', 'Beirut, Lebanon'),
    'hours' => env('STORE_HOURS', 'Mon-Sat, 9:00 - 18:00'),
    'delivery_days_min' => (int) env('STORE_DELIVERY_MIN', 3), // used in the WhatsApp order message
    'delivery_days_max' => (int) env('STORE_DELIVERY_MAX', 6),
];