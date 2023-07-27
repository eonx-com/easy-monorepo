<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bridge;

interface BridgeConstantsInterface
{
    public const PARAM_API_KEY = 'easy_bugsnag.api_key';

    public const PARAM_APP_NAME_ENV_VAR = 'easy_bugsnag.app_name_env_var';

    public const PARAM_AWS_ECS_FARGATE_META_STORAGE_FILENAME = 'easy_bugsnag.aws_ecs_fargate_meta_storage_filename';

    public const PARAM_AWS_ECS_FARGATE_META_URL = 'easy_bugsnag.aws_ecs_fargate_meta_url';

    public const PARAM_DOCTRINE_DBAL_CONNECTIONS = 'easy_bugsnag.doctrine_dbal.connections';

    public const PARAM_DOCTRINE_DBAL_ENABLED = 'easy_bugsnag.doctrine_dbal.enabled';

    public const PARAM_PROJECT_ROOT = 'easy_bugsnag.project_root';

    public const PARAM_RELEASE_STAGE = 'easy_bugsnag.release_stage';

    public const PARAM_RUNTIME = 'easy_bugsnag.runtime';

    public const PARAM_RUNTIME_VERSION = 'easy_bugsnag.runtime_version';

    public const PARAM_SENSITIVE_DATA_SANITIZER_ENABLED = 'easy_bugsnag.sensitive_data_sanitizer_enabled';

    public const PARAM_SESSION_TRACKING_CACHE_DIRECTORY = 'easy_bugsnag.session_tracking_cache_directory';

    public const PARAM_SESSION_TRACKING_CACHE_EXPIRES_AFTER = 'easy_bugsnag.session_tracking_cache_expires_after';

    public const PARAM_SESSION_TRACKING_CACHE_NAMESPACE = 'easy_bugsnag.session_tracking_cache_namespace';

    public const PARAM_SESSION_TRACKING_EXCLUDE_URLS = 'easy_bugsnag.session_tracking_exclude_urls';

    public const PARAM_SESSION_TRACKING_EXCLUDE_URLS_DELIMITER = 'easy_bugsnag.session_tracking_exclude_urls_delimiter';

    public const PARAM_STRIP_PATH = 'easy_bugsnag.strip_path';

    public const SERVICE_REQUEST_RESOLVER = 'easy_bugsnag.request_resolver';

    public const SERVICE_SESSION_TRACKING_CACHE = 'easy_bugsnag.session_tracking.cache';

    public const SERVICE_SHUTDOWN_STRATEGY = 'easy_bugsnag.shutdown_strategy';

    public const TAG_CLIENT_CONFIGURATOR = 'easy_bugsnag.client_configurator';
}
