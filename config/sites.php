<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Expert (full UI) hosts — admin only
    |--------------------------------------------------------------------------
    */
    'expert_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('EXPERT_SITE_HOSTS', 'expert.astromotto.hu,expert.localhost'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Public (simplified UI) hosts
    |--------------------------------------------------------------------------
    */
    'public_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('PUBLIC_SITE_HOSTS', 'astromotto.hu,www.astromotto.hu,localhost,127.0.0.1'))
    ))),

    'public_url' => env('PUBLIC_SITE_URL', 'https://astromotto.hu'),

    'expert_url' => env('EXPERT_SITE_URL', 'https://expert.astromotto.hu'),

];
