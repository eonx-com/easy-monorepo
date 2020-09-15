<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_IS_VERBOSE = 'easy_error_handler.param_is_verbose';

    /**
     * @var string
     */
    public const PARAM_RESPONSE_KEYS = 'easy_error_handler.param_response_keys';

    /**
     * @var string
     */
    public const PARAM_TRANSLATION_DOMAIN = 'easy_error_handler.param_translation_domain';

    /**
     * @var string
     */
    public const TAG_ERROR_REPORTER_PROVIDER = 'easy_error_handler.error_reporter_provider';

    /**
     * @var string
     */
    public const TAG_ERROR_RESPONSE_BUILDER_PROVIDER = 'easy_error_handler.error_response_builder_provider';
}
