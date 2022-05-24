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
    public const PARAM_APP_NAME_ENV_VAR = 'easy_bugsnag.app_name_env_var';

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
    public const PARAM_PROJECT_ROOT = 'easy_bugsnag.project_root';

    /**
     * @var string
     */
    public const PARAM_RELEASE_STAGE = 'easy_bugsnag.release_stage';

    /**
     * @var string
     */
    public const PARAM_RUNTIME = 'easy_bugsnag.runtime';

    /**
     * @var string
     */
    public const PARAM_RUNTIME_VERSION = 'easy_bugsnag.runtime_version';

    /**
     * @var string
     */
    public const PARAM_SENSITIVE_DATA_SANITIZER_ENABLED = 'easy_bugsnag.sensitive_data_sanitizer_enabled';

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
    public const PARAM_STRIP_PATH = 'easy_bugsnag.strip_path';

    /**
     * @var string
     */
    public const SERVICE_REQUEST_RESOLVER = 'easy_bugsnag.request_resolver';

    /**
     * @var string
     */
    public const SERVICE_SESSION_TRACKING_CACHE = 'easy_bugsnag.session_tracking.cache';

    /**
     * @var string
     */
    public const SERVICE_SHUTDOWN_STRATEGY = 'easy_bugsnag.shutdown_strategy';

    /**
     * @var string
     */
    public const TAG_CLIENT_CONFIGURATOR = 'easy_bugsnag.client_configurator';
}
