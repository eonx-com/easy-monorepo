<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use BackedEnum;
use Exception;
use InvalidArgumentException;
use Throwable;

abstract class BaseException extends Exception implements
    TranslatableExceptionInterface,
    LogLevelAwareExceptionInterface,
    StatusCodeAwareExceptionInterface,
    SubCodeAwareExceptionInterface
{
    use LogLevelAwareExceptionTrait;
    use StatusCodeAwareExceptionTrait;
    use SubCodeAwareExceptionTrait;
    use TranslatableExceptionTrait;

    public function __construct(?string $message = null, null|int|BackedEnum $code = null, ?Throwable $previous = null)
    {
        $codeValue = null;

        if ($code !== null) {
            if ($code instanceof BackedEnum && \is_int($code->value) === false) {
                throw new InvalidArgumentException(
                    \sprintf('The backed case of the "%s" backed enum has to be an integer.', $code::class)
                );
            }

            if ($code instanceof BackedEnum && \is_int($code->value)) {
                $codeValue = $code->value;
            }

            if (\is_int($code)) {
                $codeValue = $code;
            }
        }

        parent::__construct($message ?? '', $codeValue ?? 0, $previous);
    }
}
