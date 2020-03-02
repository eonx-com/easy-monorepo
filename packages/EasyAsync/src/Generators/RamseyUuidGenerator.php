<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Generators;

use EonX\EasyAsync\Exceptions\UnableToGenerateUuidException;
use EonX\EasyAsync\Interfaces\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

final class RamseyUuidGenerator implements UuidGeneratorInterface
{
    /**
     * Generate UUID V4.
     *
     * @return string
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateUuidException
     */
    public function generate(): string
    {
        try {
            return Uuid::uuid4()->toString();
            // @codeCoverageIgnoreStart
        } catch (\Exception $exception) {
            throw new UnableToGenerateUuidException($exception->getMessage(), $exception->getCode(), $exception);
        }
        // @codeCoverageIgnoreEnd
    }
}
