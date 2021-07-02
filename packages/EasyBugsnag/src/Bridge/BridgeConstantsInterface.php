<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_API_KEY = 'easy_bugsnag.api_key';

    /**
     * @var string
     */
    public const PARAM_AWS_ECS_FARGATE_META_STORAGE_FILENAME = 'easy_bugsnag.aws_ecs_fargate_meta_storage_filename';

    /**
     * @var string
     */
    public const PARAM_AWS_ECS_FARGATE_META_URL = 'easy_bugsnag.aws_ecs_fargate_meta_url';

    /**
     * @var string
     */
    public const PARAM_DOCTRINE_DBAL_CONNECTIONS = 'easy_bugsnag.doctrine_dbal.connections';

    /**
     * @var string
     */
    public const PARAM_DOCTRINE_DBAL_ENABLED = 'easy_bugsnag.doctrine_dbal.enabled';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_CACHE_DIRECTORY = 'easy_bugsnag.session_tracking_cache_directory';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_CACHE_EXPIRES_AFTER = 'easy_bugsnag.session_tracking_cache_expires_after';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_CACHE_NAMESPACE = 'easy_bugsnag.session_tracking_cache_namespace';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_EXCLUDE_URLS = 'easy_bugsnag.session_tracking_exclude_urls';

    /**
     * @var string
     */
    public const PARAM_SESSION_TRACKING_EXCLUDE_URLS_DELIMITER = 'easy_bugsnag.session_tracking_exclude_urls_delimiter';

    /**
     * @var string
     */
    public const SERVICE_SESSION_TRACKING_CACHE = 'easy_bugsnag.session_tracking.cache';

    /**
     * @var string
     */
    public const TAG_CLIENT_CONFIGURATOR = 'easy_bugsnag.client_configurator';
}
