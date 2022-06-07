<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorHandlerAwareInterface;
use EonX\EasyErrorHandler\Traits\ErrorHandlerAwareTrait;
use Throwable;

final class ExtendedExceptionBuilder extends AbstractErrorResponseBuilder implements ErrorHandlerAwareInterface
{
    use ErrorHandlerAwareTrait;

    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorDetailsResolverInterface
     */
    private $errorDetailsResolver;

    /**
     * @var string
     */
    private $exceptionKey;

    /**
     * @var string[]
     */
    private $keys;

    /**
     * @param null|string[] $keys
     */
    public function __construct(
        ErrorDetailsResolverInterface $errorDetailsResolver,
        ?string $exceptionKey = null,
        ?array $keys = null,
        ?int $priority = null
    ) {
        $this->errorDetailsResolver = $errorDetailsResolver;
        $this->exceptionKey = $exceptionKey ?? 'exception';
        $this->keys = $keys ?? [];

        parent::__construct($priority);
    }

    public function buildData(Throwable $throwable, array $data): array
    {
        // Skip if not verbose
        if ($this->errorHandler->isVerbose() === false) {
            return parent::buildData($throwable, $data);
        }

        $details = $this->errorDetailsResolver->resolveSimpleDetails($throwable);

        $exception = [
            $this->getKey('class') => $details['class'],
            $this->getKey('file') => $details['file'],
            $this->getKey('line') => $details['line'],
            $this->getKey('message') => $details['message'],
            $this->getKey('trace') => $details['trace'],
        ];

        $data[$this->exceptionKey] = $exception;

        return parent::buildData($throwable, $data);
    }

    private function getKey(string $name): string
    {
        return $this->keys[$name] ?? $name;
    }
}
