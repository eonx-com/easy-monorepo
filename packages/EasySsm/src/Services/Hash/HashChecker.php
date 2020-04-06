<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

final class HashChecker implements HashCheckerInterface
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
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params
     */
    public function checkHash(string $name, array $params): bool
    {
        $localHash = $this->hashRepository->get($name);

        if ($localHash === null) {
            // Maybe throw exception here to clearly identify there is no local hash for given name.
            return false;
        }

        return $this->checkHashes($localHash, $this->hashCalculator->calculate($params));
    }

    public function checkHashes(string $hash1, string $hash2): bool
    {
        return $hash1 === $hash2;
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params1
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params2
     */
    public function checkHashesForParams(array $params1, array $params2): bool
    {
        return $this->checkHashes(
            $this->hashCalculator->calculate($params1),
            $this->hashCalculator->calculate($params2)
        );
    }
}
