<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use BackedEnum;
use Throwable;

final class CodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    private readonly array $exceptionToCode;

    public function __construct(
        string $key,
        ?int $priority = null,
        ?array $exceptionToCode = null,
    ) {
        $this->exceptionToCode = $exceptionToCode ?? [];

        parent::__construct($key, $priority);
    }

    /**
     * Some exceptions have the code as string, so we need return type to be int or string.
     *
     * @see https://www.php.net/manual/en/class.pdoexception.php#95812
     */
    protected function doBuildValue(Throwable $throwable, array $data): int|string
    {
        foreach ($this->exceptionToCode as $class => $code) {
            if (\is_a($throwable, $class)) {
                return $code instanceof BackedEnum ? $code->value : $code;
            }
        }

        return $throwable->getCode();
    }
}
