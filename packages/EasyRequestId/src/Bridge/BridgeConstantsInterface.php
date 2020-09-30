<?php

declare(strict_types=1);

namespace EonX\EasyRequestId\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_CORRELATION_ID_KEY = 'easy_request_id.correlation_id_key';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_CORRELATION_ID_HEADER = 'easy_request_id.default_correlation_id_header';

    /**
     * @var string
     */
    public const PARAM_DEFAULT_REQUEST_ID_HEADER = 'easy_request_id.default_request_id_header';

    /**
     * @var string
     */
    public const PARAM_REQUEST_ID_KEY = 'easy_request_id.request_id_key';

    /**
     * @var string
     */
    public const TAG_CORRELATION_ID_RESOLVER = 'easy_request_id.correlation_id_resolver';

    /**
     * @var string
     */
    public const TAG_REQUEST_ID_RESOLVER = 'easy_request_id.request_id_resolver';
}
