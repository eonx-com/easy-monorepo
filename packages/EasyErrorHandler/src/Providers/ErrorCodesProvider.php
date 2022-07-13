<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\ErrorHandler\Error\ClassNotFoundError;

final class ErrorCodesProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'ERROR_';

    /**
     * @param class-string|null $errorCodesInterface
     */
    public function __construct(private ?string $errorCodesInterface = null)
    {
    }

    public function provide(): array
    {
        if ($this->errorCodesInterface === null) {
            return [];
        }

        try {
            $reflection = new ReflectionClass($this->errorCodesInterface);
        } catch (ReflectionException $exception) {
            throw new ClassNotFoundError($exception->getMessage(), $exception);
        }

        return \array_filter(
            $reflection->getConstants(),
            static fn ($name) => \str_starts_with($name, self::ERROR_CODE_NAME_PREFIX),
            \ARRAY_FILTER_USE_KEY
        );
    }
}
