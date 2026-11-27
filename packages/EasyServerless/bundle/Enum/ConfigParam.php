<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Enum;

enum ConfigParam: string
{
    case AppMetricNamespace = 'easy_serverless.app_metric.namespace';

    case AssetsSeparateDomainEnabled = 'easy_serverless.assets_separate_domain.enabled';

    case AssetsSeparateDomainUrl = 'easy_serverless.assets_separate_domain.url';

    case SnapStartClassMapWarmerNamespaces = 'easy_serverless.snap_start.warmers.class_map.namespaces';

    case SnapStartHttpRequestWarmerHost = 'easy_serverless.snap_start.warmers.http_request.host';

    case SnapStartHttpRequestWarmerPath = 'easy_serverless.snap_start.warmers.http_request.path';
}
