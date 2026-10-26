<?php

return [

    // 'staging' or 'production'
    'env' => 'production',

    // Where users land on redirects (must NOT contain "payments/paytm")
    'result_url' => 'http://localhost:8000/admin/submit-articles',

    'staging' => [
        'mid'           => 'Rabind71484823962045',
        'key'           => 'H2KCt3w9iIw8&X71',
        'website'       => 'WEBSTAGING',
        'industry_type' => 'Retail',
        'channel_id'    => 'WEB',
        'host'          => 'https://securestage.paytmpayments.com',
    ],

    'production' => [
        'mid'           => 'Rabind71484823962045',
        'key'           => 'H2KCt3w9iIw8&X71',
        'website'       => 'DEFAULT',
        'industry_type' => 'Retail',
        'channel_id'    => 'WEB',
        'host'          => 'https://secure.paytm.in',
    ],
];