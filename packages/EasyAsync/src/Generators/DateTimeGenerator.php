<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Generators;

use EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException;
use EonX\EasyAsync\Interfaces\DateTimeGeneratorInterface;

final class DateTimeGenerator implements DateTimeGeneratorInterface
{
    /**
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * DateTimeGenerator constructor.
     *
     * @param null|\DateTimeZone $timezone
     */
    public function __construct(?\DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone ?? new \DateTimeZone('UTC');
    }

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
    public function fromString(string $dateTime, ?string $format = null): \DateTime
    {
        try {
            return \DateTime::createFromFormat($format ?? self::DATE_FORMAT, $dateTime, $this->timezone);
        } catch (\Exception $exception) {
            throw new UnableToGenerateDateTimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Get datetime for now.
     *
     * @return \DateTime
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     */
    public function now(): \DateTime
    {
        try {
            return new \DateTime('now', $this->timezone);
        } catch (\Exception $exception) {
            throw new UnableToGenerateDateTimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
