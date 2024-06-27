<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use Throwable;

final class CodeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'code';

    /**
     * Some exceptions have the code as string, so we need return type to be int or string.
     *
     * @see https://www.php.net/manual/en/class.pdoexception.php#95812
     */
    protected function doBuildValue(Throwable $throwable, array $data): int|string
    {
        return $throwable->getCode();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}
