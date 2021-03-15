<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
interface DateTimeGeneratorInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function fromString(string $dateTime, ?string $format = null): \DateTime;

    public function now(): \DateTime;
}
