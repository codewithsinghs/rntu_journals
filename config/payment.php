<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard Article Processing Fee
    |--------------------------------------------------------------------------
    | Amount (in INR) charged to an author when no waiver/concession applies.
    | Override in .env with ARTICLE_STANDARD_FEE=2500 if needed.
    */
    'standard_fee' => (float) env('ARTICLE_STANDARD_FEE', 1000.00),

    'currency' => env('ARTICLE_PAYMENT_CURRENCY', 'INR'),

];