<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Array in query attribute name
    |--------------------------------------------------------------------------
    |
    | This config is used to resolve the pagination data when it is expected
    | in the query parameters of the request as an array. This config is the
    | name of the query parameter containing the pagination data array.
    |
    | Example:
    | For this config as "page", the resolver will look in the query for:
    | "<your-url>?page[<number_attr>]=1&page[<size_attr>]=15"
    |
    */
    'array_in_query_attr' => \env('PAGINATION_ARRAY_IN_QUERY_ATTR', 'page'),
    /*
    |--------------------------------------------------------------------------
    | StartSize EasyPagination
    |--------------------------------------------------------------------------
    |
    | This config contains the names of the attributes to use to resolve the
    | start_size pagination data, and also their default values if not set
    | on the given request.
    |
    */
    'start_size' => [
        'size_attribute' => \env('PAGINATION_PAGE_SIZE_ATTRIBUTE', 'perPage'),
        'size_default' => \env('PAGINATION_PAGE_SIZE_DEFAULT', 15),
        'start_attribute' => \env('PAGINATION_PAGE_START_ATTRIBUTE', 'page'),
        'start_default' => \env('PAGINATION_PAGE_START_DEFAULT', 1),
    ],
];
