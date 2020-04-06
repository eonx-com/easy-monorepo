<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Filesystem;

use EonX\EasySsm\Services\Hash\HashCalculatorInterface;
use EonX\EasySsm\Services\Hash\HashRepositoryInterface;

final class HashDumper implements HashDumperInterface
{
    /**
     * @var \EonX\EasySsm\Services\Hash\HashCalculatorInterface
     */
    private $hashCalculator;

    /**
     * @var \EonX\EasySsm\Services\Hash\HashRepositoryInterface
     */
    private $hashRepository;

    public function __construct(HashCalculatorInterface $hashCalculator, HashRepositoryInterface $hashRepository)
    {
        $this->hashCalculator = $hashCalculator;
        $this->hashRepository = $hashRepository;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     */
    public function dumpHash(string $name, array $parameters): void
    {
        $this->hashRepository->save($name, $this->hashCalculator->calculate($parameters));
    }
}
