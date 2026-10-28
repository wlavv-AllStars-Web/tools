<?php

return [
    /*
     * Safety switch: the wizard always allows review, but price writes are
     * denied unless this is explicitly enabled in the production environment.
     */
    'apply_enabled' => env('CURRENCY_PRICE_UPDATES_APPLY_ENABLED', false),
];
