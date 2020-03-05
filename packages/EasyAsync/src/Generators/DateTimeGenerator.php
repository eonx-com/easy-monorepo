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

    public function __construct(?\DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone ?? new \DateTimeZone('UTC');
    }

    public function fromString(string $dateTime, ?string $format = null): \DateTime
    {
        try {
            return \DateTime::createFromFormat($format ?? self::DATE_FORMAT, $dateTime, $this->timezone);
            // @codeCoverageIgnoreStart
        } catch (\Throwable $exception) {
            throw new UnableToGenerateDateTimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
        // @codeCoverageIgnoreEnd
    }

    public function now(): \DateTime
    {
        try {
            return new \DateTime('now', $this->timezone);
            // @codeCoverageIgnoreStart
        } catch (\Throwable $exception) {
            throw new UnableToGenerateDateTimeException($exception->getMessage(), $exception->getCode(), $exception);
        }
        // @codeCoverageIgnoreEnd
    }
}
