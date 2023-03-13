<?php

declare(strict_types=1);

namespace EonX\EasyRandom;

use EonX\EasyRandom\Bridge\BridgeConstantsInterface;
use EonX\EasyRandom\Enums\UuidVersion;
use EonX\EasyRandom\Exceptions\UuidV4GeneratorNotSetException;
use EonX\EasyRandom\Exceptions\UuidV6GeneratorNotSetException;
use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;
use EonX\EasyRandom\Interfaces\RandomStringInterface;
use EonX\EasyRandom\Interfaces\UuidV4GeneratorInterface;
use EonX\EasyRandom\Interfaces\UuidV6GeneratorInterface;

final class RandomGenerator implements RandomGeneratorInterface
{
    private UuidVersion $defaultUuidVersion;

    public function __construct(
        ?UuidVersion $defaultUuidVersion = null,
        private ?UuidV4GeneratorInterface $uuidV4Generator = null,
        private ?UuidV6GeneratorInterface $uuidV6Generator = null
    ) {
        $this->defaultUuidVersion = $defaultUuidVersion
            ?? UuidVersion::from(BridgeConstantsInterface::DEFAULT_UUID_VERSION);
    }

    public function randomInteger(?int $min = null, ?int $max = null): int
    {
        return \random_int($min ?? 0, $max ?? \PHP_INT_MAX);
    }

    public function randomString(int $length): RandomStringInterface
    {
        return new RandomString($length);
    }

    /**
     * @deprecated Will be removed in 5.0.
     */
    public function setUuidV4Generator(UuidV4GeneratorInterface $uuidV4Generator): RandomGeneratorInterface
    {
        $this->uuidV4Generator = $uuidV4Generator;

        return $this;
    }

    public function uuid(?UuidVersion $version = null): string
    {
        $version ??= $this->defaultUuidVersion;

        return match ($version) {
            UuidVersion::V4 => $this->generateUuidV4(),
            UuidVersion::V6 => $this->generateUuidV6(),
        };
    }

    /**
     * @deprecated Will be removed in 5.0. Use the uuid(UuidVersion::V4) instead.
     */
    public function uuidV4(): string
    {
        return $this->generateUuidV4();
    }

    private function generateUuidV4(): string
    {
        if ($this->uuidV4Generator !== null) {
            return $this->uuidV4Generator->generate();
        }

        throw new UuidV4GeneratorNotSetException('The UUID V4 generator must be set by calling setUuidV4Generator()');
    }

    private function generateUuidV6(): string
    {
        if ($this->uuidV6Generator !== null) {
            return $this->uuidV6Generator->generate();
        }

        throw new UuidV6GeneratorNotSetException('The UUID V6 generator must be set by calling setUuidV6Generator()');
    }
}
