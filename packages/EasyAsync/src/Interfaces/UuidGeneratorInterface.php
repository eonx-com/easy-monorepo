<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface UuidGeneratorInterface
{
    /**
     * Generate UUID V4.
     *
     * @return string
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateUuidException
     */
    public function generate(): string;
}
