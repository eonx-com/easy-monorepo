<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Providers;

use EonX\EasyEncryption\Exceptions\CircularReferenceDetectedException;
use EonX\EasyEncryption\Exceptions\CouldNotProvideEncryptionKeyException;
use EonX\EasyEncryption\Interfaces\EncryptionKeyFactoryInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyProviderInterface;
use EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;
use ParagonIE\Halite\EncryptionKeyPair;
use ParagonIE\Halite\Symmetric\EncryptionKey;

final class DefaultEncryptionKeyProvider implements EncryptionKeyProviderInterface
{
    /**
     * @var \EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface[]
     */
    private array $keyResolvers;

    /**
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey[]|\ParagonIE\Halite\EncryptionKeyPair[]
     */
    private array $resolved = [];

    /**
     * @var string[]
     */
    private array $resolving = [];

    /**
     * @param iterable<\EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface> $keyResolvers
     */
    public function __construct(
        private EncryptionKeyFactoryInterface $keyFactory,
        iterable $keyResolvers,
    ) {
        /** @var \EonX\EasyEncryption\Interfaces\EncryptionKeyResolverInterface[] $filteredAndSortedKeyResolvers */
        $filteredAndSortedKeyResolvers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($keyResolvers, EncryptionKeyResolverInterface::class)
        );
        $this->keyResolvers = $filteredAndSortedKeyResolvers;
    }

    public function getKey(string $keyName): EncryptionKey|EncryptionKeyPair
    {
        if (isset($this->resolved[$keyName])) {
            return $this->resolved[$keyName];
        }

        if (isset($this->resolving[$keyName])) {
            $this->circularReference($keyName);
        }

        $this->resolving[$keyName] = $keyName;

        try {
            return $this->doGetKey($keyName);
        } catch (\Throwable $throwable) {
            throw new CouldNotProvideEncryptionKeyException(
                \sprintf('Could not provide encryption key: %s', $throwable->getMessage()),
                $throwable->getCode(),
                $throwable
            );
        } finally {
            unset($this->resolving[$keyName]);
        }
    }

    public function hasKey(string $keyName): bool
    {
        foreach ($this->keyResolvers as $keyResolver) {
            if ($keyResolver->supportsKey($keyName)) {
                return true;
            }
        }

        return false;
    }

    public function reset(): void
    {
        $this->resolved = [];
    }

    private function circularReference(string $keyName): void
    {
        $this->resolving = [];

        throw new CircularReferenceDetectedException(\sprintf(
            'Circular reference detected for key "%s"',
            $keyName
        ));
    }

    /**
     * @return \ParagonIE\Halite\EncryptionKeyPair|\ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private function doGetKey(string $keyName)
    {
        foreach ($this->keyResolvers as $keyResolver) {
            if ($keyResolver->supportsKey($keyName)) {
                return $this->keyFactory->create($keyResolver->resolveKey($keyName));
            }
        }

        throw new CouldNotProvideEncryptionKeyException(\sprintf('No resolver found for key "%s"', $keyName));
    }
}
