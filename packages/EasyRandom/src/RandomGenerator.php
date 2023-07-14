<?php

declare(strict_types=1);

namespace EonX\EasyRandom;

use EonX\EasyRandom\Exceptions\UuidV4GeneratorNotSetException;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    private ?UuidV4GeneratorInterface $uuidV4Generator = null;

    public function randomInteger(?int $min = null, ?int $max = null): int
    {
        return \random_int($min ?? 0, $max ?? \PHP_INT_MAX);
    }

    public function randomString(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }

    public function setUuidV4Generator(UuidV4GeneratorInterface $uuidV4Generator): RandomGeneratorInterface
    {
        $this->uuidV4Generator = $uuidV4Generator;

        return $this;
    }

    public function uuidV4(): string
    {
        if ($this->uuidV4Generator !== null) {
            return $this->uuidV4Generator->generate();
        }

        throw new UuidV4GeneratorNotSetException('The UUID V4 generator must be set by calling setUuidV4Generator()');
    }
}
