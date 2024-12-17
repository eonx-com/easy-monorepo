<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\Provider;

use EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCode;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\ErrorHandler\Error\ClassNotFoundError;

final readonly class ErrorCodesFromInterfaceProvider implements ErrorCodesProviderInterface
{
    private const ERROR_CODE_NAME_PREFIX = 'ERROR_';

    /**
     * @param class-string|null $errorCodesInterface
     */
    public function __construct(
        private ?string $errorCodesInterface = null,
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
        /** @var int $code */
        foreach ($constants as $name => $code) {
            if (\str_starts_with($name, self::ERROR_CODE_NAME_PREFIX) === false) {
                continue;
            }
            $errorCodes[] = new ErrorCode(
                originalName: $name,
                errorCode: $code,
                splitName: \explode('_', $name),
                groupSeparator: '_'
            );
        }

        return $errorCodes;
    }
}
