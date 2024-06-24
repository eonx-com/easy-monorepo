<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle\Enum;

enum ConfigParam: string
{
    case AdvancedSearchFilterIriFields = 'easy_api_platform.advanced_search_filter.iri_fields';

    case CustomPaginatorEnabled = 'easy_api_platform.custom_paginator_enabled';
}
