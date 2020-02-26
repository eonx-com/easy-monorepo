<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface DateTimeGeneratorInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Get datetime for now.
     *
     * @return \DateTime
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function now(): \DateTime;
}
