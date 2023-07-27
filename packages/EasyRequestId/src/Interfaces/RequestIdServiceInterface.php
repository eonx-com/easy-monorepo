<?php
declare(strict_types=1);

namespace EonX\EasyRequestId\Interfaces;

interface RequestIdServiceInterface
{
    public const DEFAULT_HTTP_HEADER_CORRELATION_ID = 'X-CORRELATION-ID';

    public const DEFAULT_HTTP_HEADER_REQUEST_ID = 'X-REQUEST-ID';

    public const KEY_RESOLVED_CORRELATION_ID = 'resolved_correlation_id';

    public const KEY_RESOLVED_REQUEST_ID = 'resolved_request_id';

    public function getCorrelationId(): string;

    public function getCorrelationIdHeaderName(): string;

    public function getRequestId(): string;

    public function getRequestIdHeaderName(): string;

    public function setResolver(callable $resolver): self;
}
