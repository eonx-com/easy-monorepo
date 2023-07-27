<?php
declare(strict_types=1);

use EonX\EasyPagination\Bridge\BridgeConstantsInterface;

return [
    'pagination' => [
        'page_attribute' => \env('PAGINATION_PAGE_ATTRIBUTE', BridgeConstantsInterface::PARAM_PAGE_ATTRIBUTE),
        'page_default' => \env('PAGINATION_PAGE_DEFAULT', BridgeConstantsInterface::PARAM_PAGE_DEFAULT),
        'per_page_attribute' => \env(
            'PAGINATION_PER_PAGE_ATTRIBUTE',
            BridgeConstantsInterface::PARAM_PER_PAGE_ATTRIBUTE
        ),
        'per_page_default' => \env('PAGINATION_PER_PAGE_DEFAULT', BridgeConstantsInterface::PARAM_PER_PAGE_DEFAULT),
    ],

    'use_default_resolver' => \env('PAGINATION_USE_DEFAULT_RESOLVER', true),
];
