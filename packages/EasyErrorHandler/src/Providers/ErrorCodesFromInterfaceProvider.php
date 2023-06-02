<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Providers;

use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodeDto;
use EonX\EasyErrorHandler\Interfaces\ErrorCodesProviderInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\ErrorHandler\Error\ClassNotFoundError;

final class ErrorCodesFromInterfaceProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'ERROR_';

    /**
     * @param class-string|null $errorCodesInterface
     */
    public function __construct(
        private readonly ?string $errorCodesInterface = null
    ) {
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

        $constants = $reflection->getConstants();
        $errorCodes = [];
        foreach ($constants as $name => $code) {
            if (\str_starts_with($name, self::ERROR_CODE_NAME_PREFIX) === false) {
                continue;
            }
            $errorCodes[] = new ErrorCodeDto(
                originalName: $name,
                errorCode: $code,
                splitName: \explode('_', $name),
                groupSeparator: '_'
            );
        }

        return $errorCodes;
    }
}
