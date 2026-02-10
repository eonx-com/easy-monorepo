<?php
declare(strict_types=1);

namespace EonX\EasyServerless\AppMetric\Client;

use EonX\EasyServerless\AppMetric\ValueObject\AppMetricInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class AppMetricClient implements AppMetricClientInterface
{
    private const METRIC_PARAM_KEY_PATTERN = '%s_%s';

    private const PATTERN = '[appMetric][%s]';

    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
        private ?string $namespace = null,
    ) {
    }

    public function sendMetric(AppMetricInterface $appMetric): void
    {
        $namespace = $appMetric->getNamespace() ?? $this->namespace;
        $nameInLog = \is_string($namespace) && $namespace !== ''
            ? \sprintf('%s_%s', $namespace, $appMetric->getName())
            : $appMetric->getName();

        // Our current setup uses Datadog log ingestion rules to generate metrics
        // so simply logging with a specific pattern is enough
        $this->logger->debug(\sprintf(self::PATTERN, $nameInLog), [
            'appMetric' => [
                'dimensions' => $this->resolveDimensions($appMetric->getDimensions(), $namespace),
                'name' => $appMetric->getName(),
                'namespace' => $namespace,
            ],
        ]);
    }

    public function sendMetricsBatch(array $appMetrics): void
    {
        foreach ($appMetrics as $appMetric) {
            if ($appMetric instanceof AppMetricInterface) {
                $this->sendMetric($appMetric);
            }
        }
    }

    private function resolveDimensions(array $dimensions, ?string $namespace = null): array
    {
        if ($namespace === null || $namespace === '') {
            return $dimensions;
        }

        $resolvedDimensions = [];
        foreach ($dimensions as $key => $value) {
            $newKey = \sprintf(self::METRIC_PARAM_KEY_PATTERN, $namespace, $key);
            $resolvedDimensions[$newKey] = $value;
        }

        return $resolvedDimensions;
    }
}
