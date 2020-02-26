<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface DateTimeGeneratorInterface
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Generate datetime from given string and optional format.
     *
     * @param string $dateTime
     * @param null|string $format
     *
     * @return \DateTime
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function fromString(string $dateTime, ?string $format = null): \DateTime;

    /**
     * Get datetime for now.
     *
     * @return \DateTime
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function now(): \DateTime;
}
