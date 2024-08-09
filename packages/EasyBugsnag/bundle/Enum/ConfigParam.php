<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Bundle\Enum;

enum ConfigParam: string
{
    case ApiKey = 'easy_bugsnag.api_key';

    case AppNameEnvVar = 'easy_bugsnag.app_name_env_var';

    case AwsEcsFargateMetaStorageFilename = 'easy_bugsnag.aws_ecs_fargate_meta_storage_filename';

    case AwsEcsFargateMetaUrl = 'easy_bugsnag.aws_ecs_fargate_meta_url';

    case DoctrineDbalConnections = 'easy_bugsnag.doctrine_dbal.connections';

    case ProjectRoot = 'easy_bugsnag.project_root';

    case ReleaseStage = 'easy_bugsnag.release_stage';

    case Runtime = 'easy_bugsnag.runtime';

    case RuntimeVersion = 'easy_bugsnag.runtime_version';

    case SensitiveDataSanitizerEnabled = 'easy_bugsnag.sensitive_data_sanitizer_enabled';

    case SessionTrackingCacheDirectory = 'easy_bugsnag.session_tracking_cache_directory';

    case SessionTrackingCacheExpiresAfter = 'easy_bugsnag.session_tracking_cache_expires_after';

    case SessionTrackingCacheNamespace = 'easy_bugsnag.session_tracking_cache_namespace';

    case SessionTrackingExcludeUrls = 'easy_bugsnag.session_tracking_exclude_urls';

    case SessionTrackingExcludeUrlsDelimiter = 'easy_bugsnag.session_tracking_exclude_urls_delimiter';

    case StripPath = 'easy_bugsnag.strip_path';
}
