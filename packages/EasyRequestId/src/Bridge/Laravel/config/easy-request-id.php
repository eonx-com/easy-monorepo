<?php
declare(strict_types=1);

use EonX\EasyRequestId\Interfaces\ResolverInterface;

return [
    /**
     * Enable default resolver.
     */
    'default_resolver' => true,

    /**
     * Header used by default resolver to resolve correlation-id.
     */
    'default_correlation_id_header' => ResolverInterface::DEFAULT_CORRELATION_ID_HEADER,

    /**
     * Header used by default resolver to resolve request-id.
     */
    'default_request_id_header' => ResolverInterface::DEFAULT_REQUEST_ID_HEADER,
];
