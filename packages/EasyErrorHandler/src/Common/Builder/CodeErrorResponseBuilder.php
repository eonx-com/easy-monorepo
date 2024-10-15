<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use BackedEnum;
use Throwable;

final class CodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public function __construct(
        string $key,
        ?int $priority = null,
        private ?array $exceptionCodes = null,
    ) {
        $this->exceptionCodes ??= [];

        parent::__construct($key, $priority);
    }

    /**
     * Some exceptions have the code as string, so we need return type to be int or string.
     *
     * @see https://www.php.net/manual/en/class.pdoexception.php#95812
     */
    protected function doBuildValue(Throwable $throwable, array $data): int|string
    {
        if (isset($this->exceptionCodes[$throwable::class])) {
            return $this->exceptionCodes[$throwable::class] instanceof BackedEnum
                ? $this->exceptionCodes[$throwable::class]->value
                : $this->exceptionCodes[$throwable::class];
        }

        return $throwable->getCode();
    }
}
