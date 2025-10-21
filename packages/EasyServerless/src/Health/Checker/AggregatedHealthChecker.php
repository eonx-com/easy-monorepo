<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\Checker;

use EonX\EasyServerless\Health\ValueObject\HealthCheckResult;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class AggregatedHealthChecker
{
    /**
     * @var \EonX\EasyServerless\Health\Checker\HealthCheckerInterface[]
     */
    private array $checkers;

    /**
     * @param iterable<\EonX\EasyServerless\Health\Checker\HealthCheckerInterface> $checkers
     */
    public function __construct(
        iterable $checkers,
        private ?LoggerInterface $logger = null,
    ) {
        $this->checkers = CollectorHelper::filterByClassAsArray($checkers, HealthCheckerInterface::class);
    }

    /**
     * @return array<string, \EonX\EasyServerless\Health\ValueObject\HealthCheckResult>
     */
    public function check(): array
    {
        $checks = [];

        foreach ($this->checkers as $checker) {
            try {
                $result = $checker->check();
            } catch (Throwable $throwable) {
                $result = new HealthCheckResult(false, $throwable->getMessage());
            }

            $checks[$checker->getName()] = $result;

            $this->logger?->debug(\sprintf(
                'Health check "%s" result: %s',
                $checker->getName(),
                $result->isHealthy() ? 'healthy' : 'unhealthy'
            ), ['reason' => $result->getReason()]);
        }

        return $checks;
    }
}
