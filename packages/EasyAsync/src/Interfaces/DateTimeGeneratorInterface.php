<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface DateTimeGeneratorInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function fromString(string $dateTime, ?string $format = null): \DateTime;

    public function now(): \DateTime;
}
