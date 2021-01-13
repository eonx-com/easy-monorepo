<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Builders;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use EonX\EasyErrorHandler\Builders\AbstractErrorResponseBuilder;
use Throwable;

final class ApiPlatformValidationExceptionBuilder extends AbstractErrorResponseBuilder
{
    /**
     * @var string[]
     */
    private $keys;

    /**
     * @param null|mixed[] $keys
     */
    public function __construct(?array $keys = null, ?int $priority = null)
    {
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function buildData(Throwable $throwable, array $data): array
    {
        if ($throwable instanceof ValidationException) {
            $violations = [];

            foreach ($throwable->getConstraintViolationList() as $violation) {
                $violations[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $data[$this->getKey('violations')] = $violations;
        }

        return parent::buildData($throwable, $data);
    }

    public function buildStatusCode(Throwable $throwable, ?int $statusCode = null): ?int
    {
        if ($throwable instanceof ValidationException) {
            $statusCode = 400;
        }

        return parent::buildStatusCode($throwable, $statusCode);
    }

    private function getKey(string $name): string
    {
        return $this->keys[$name] ?? $name;
    }
}
