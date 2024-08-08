<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Builder;

use Carbon\Carbon;
use Throwable;

final class TimeErrorResponseBuilder extends AbstractSingleKeyErrorResponseBuilder
{
    protected function doBuildValue(Throwable $throwable, array $data): string
    {
        return Carbon::now()->toIso8601ZuluString();
    }
}
