<?php
declare(strict_types=1);

return [
    'pagination' => [
        'page_attribute' => \env('PAGINATION_PAGE_ATTRIBUTE', 'page'),
        'page_default' => \env('PAGINATION_PAGE_DEFAULT', 1),
        'per_page_attribute' => \env('PAGINATION_PER_PAGE_ATTRIBUTE', 'perPage'),
        'per_page_default' => \env('PAGINATION_PER_PAGE_DEFAULT', 15),
        // The largest page size a client can request (larger values are reduced to it), null for no limit
        'per_page_max' => \env('PAGINATION_PER_PAGE_MAX'),
    ],

    'use_default_resolver' => \env('PAGINATION_USE_DEFAULT_RESOLVER', true),
];
