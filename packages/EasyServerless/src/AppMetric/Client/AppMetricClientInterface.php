<?php
declare(strict_types=1);

namespace EonX\EasyServerless\AppMetric\Client;

use EonX\EasyServerless\AppMetric\ValueObject\AppMetricInterface;

interface AppMetricClientInterface
{
    public function sendMetric(AppMetricInterface $appMetric): void;

    /**
     * @param \EonX\EasyServerless\AppMetric\ValueObject\AppMetricInterface[] $appMetrics
     */
    public function sendMetricsBatch(array $appMetrics): void;
}
