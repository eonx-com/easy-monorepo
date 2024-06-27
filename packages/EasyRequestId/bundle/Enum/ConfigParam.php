<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Bundle\Enum;

enum ConfigParam: string
{
    case HttpHeaderCorrelationId = 'easy_request_id.http_header.correlation_id';

    case HttpHeaderRequestId = 'easy_request_id.http_header.request_id';
}
