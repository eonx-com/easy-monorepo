<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Providers;

use EonX\EasyErrorHandler\Bridge\Symfony\Interfaces\ErrorCodes\ErrorCodesProviderInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\ErrorHandler\Error\ClassNotFoundError;

final class ErrorCodesProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'ERROR_';

    public function __construct(private string $errorCodesInterface)
    {
    }

    /**
     * @return array<string, int>
     */
    public function provide(): array
    {
        try {
            $reflection = new ReflectionClass($this->errorCodesInterface);
        } catch (ReflectionException $exception) {
            throw new ClassNotFoundError($exception->getMessage(), $exception);
        }

        return \array_filter(
            $reflection->getConstants(),
            static fn($name) => \str_starts_with($name, self::ERROR_CODE_NAME_PREFIX),
            \ARRAY_FILTER_USE_KEY
        );
    }
}
