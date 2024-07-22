<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bundle\Enum;

enum ConfigTag: string
{
    case ErrorReporterProvider = 'easy_error_handler.error_reporter_provider';

    case ErrorResponseBuilderProvider = 'easy_error_handler.error_response_builder_provider';

    case VerboseStrategyDriver = 'easy_error_handler.verbose_strategy_driver';

    case BugsnagExceptionIgnorer = 'easy_error_handler.bugsnag_exception_ignorer';
}
