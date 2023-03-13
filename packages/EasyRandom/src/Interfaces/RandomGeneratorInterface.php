<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

use EonX\EasyRandom\Enums\UuidVersion;

interface RandomGeneratorInterface
{
    public function randomInteger(?int $min = null, ?int $max = null): int;

    public function randomString(int $length): RandomStringInterface;

    /**
     * @deprecated Will be removed in 5.0.
     */
    public function setUuidV4Generator(UuidV4GeneratorInterface $uuidV4Generator): self;

    public function uuid(?UuidVersion $version = null): string;

    /**
     * @deprecated Will be removed in 5.0. Use the uuid(UuidVersion::V4) instead.
     */
    public function uuidV4(): string;
}
