<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Health\Controller;

use EonX\EasyServerless\Health\Checker\AggregatedHealthChecker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class HealthCheckController
{
    public function __construct(
        private AggregatedHealthChecker $aggregatedHealthChecker,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $statusCode = Response::HTTP_OK;
        $results = $this->aggregatedHealthChecker->check();

        foreach ($results as $result) {
            if ($result->isHealthy() === false) {
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }
        }

        return new JsonResponse(['checks' => $results], $statusCode);
    }
}
