<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Bundle\Enum;

enum ConfigParam: string
{
    case AppMetricNamespace = 'easy_serverless.app_metric.namespace';

    case AssetsSeparateDomainEnabled = 'easy_serverless.assets_separate_domain.enabled';

    case AssetsSeparateDomainUrl = 'easy_serverless.assets_separate_domain.url';
}
