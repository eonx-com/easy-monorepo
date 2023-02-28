<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Builders;

use Carbon\Carbon;
use Throwable;

final class TimeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    public const DEFAULT_KEY = 'time';

    /**
     * @param mixed[] $data
     */
    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        return Carbon::now()->toIso8601ZuluString();
    }

    protected function getDefaultKey(): string
    {
        return self::DEFAULT_KEY;
    }
}
