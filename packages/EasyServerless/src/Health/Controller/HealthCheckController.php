<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\Controller;

use EonX\EasyServerless\Health\Checker\HealthCheckerInterface;
use EonX\EasyServerless\Health\ValueObject\HealthCheckResult;
use EonX\EasyUtils\Common\Helper\CollectorHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class HealthCheckController
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
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->checkers = CollectorHelper::filterByClassAsArray($checkers, HealthCheckerInterface::class);
    }

    public function __invoke(): JsonResponse
    {
        $statusCode = Response::HTTP_OK;
        $checks = [];

        foreach ($this->checkers as $checker) {
            try {
                $result = $checker->check();
            } catch (Throwable $throwable) {
                $result = new HealthCheckResult(false, $throwable->getMessage());
            }

            if ($result->isHealthy() === false) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            $checks[$checker->getName()] = $result;

            $this->logger?->debug(\sprintf(
                'Health check "%s" result: %s',
                $checker->getName(),
                $result->isHealthy() ? 'healthy' : 'unhealthy'
            ), ['reason' => $result->getReason()]);
        }

        return new JsonResponse(['checks' => $checks], $statusCode);
    }
}
