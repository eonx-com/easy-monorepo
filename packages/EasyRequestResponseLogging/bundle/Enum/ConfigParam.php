<?php
declare(strict_types=1);

namespace EonX\EasyRequestResponseLogging\Bundle\Enum;

enum ConfigParam: string
{
    case DoNotLogPaths = 'easy_request_response_logging.do_not_log_paths';

    case LogRequestBody = 'easy_request_response_logging.log_request_body';

    case LogResponseBody = 'easy_request_response_logging.log_response_body';

    case MaxBodySize = 'easy_request_response_logging.max_body_size';

    case MaxRawBodySize = 'easy_request_response_logging.max_raw_body_size';
}
