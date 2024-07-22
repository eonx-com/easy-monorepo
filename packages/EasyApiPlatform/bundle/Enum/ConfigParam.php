<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bundle\Enum;

enum ConfigParam: string
{
    case AdvancedSearchFilterIriFields = 'easy_api_platform.advanced_search_filter.iri_fields';

    case CustomPaginatorEnabled = 'easy_api_platform.custom_paginator_enabled';

    case EasyErrorHandlerCustomSerializerExceptions
    = 'easy_api_platform.easy_error_handler_custom_serializer_exceptions';

    case EasyErrorHandlerEnabled = 'easy_api_platform.easy_error_handler_enabled';

    case EasyErrorHandlerReportExceptionsToBugsnag
    = 'easy_api_platform.easy_error_handler_report_exceptions_to_bugsnag';
}
