<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomGeneratorInterface
{
    public function randomInteger(?int $min = null, ?int $max = null): int;

    public function randomString(int $length): RandomStringInterface;

    public function setUuidV4Generator(UuidV4GeneratorInterface $uuidV4Generator): self;

    public function uuidV4(): string;
}
