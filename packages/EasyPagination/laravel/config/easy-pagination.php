<?php
declare(strict_types=1);

use EonX\EasyPagination\Bundle\Enum\ConfigParam;

return [
    'pagination' => [
        'page_attribute' => \env('PAGINATION_PAGE_ATTRIBUTE', ConfigParam::PageAttribute->value),
        'page_default' => \env('PAGINATION_PAGE_DEFAULT', ConfigParam::PageDefault->value),
        'per_page_attribute' => \env('PAGINATION_PER_PAGE_ATTRIBUTE', ConfigParam::PerPageAttribute->value),
        'per_page_default' => \env('PAGINATION_PER_PAGE_DEFAULT', ConfigParam::PerPageDefault->value),
    ],

    'use_default_resolver' => \env('PAGINATION_USE_DEFAULT_RESOLVER', true),
];
