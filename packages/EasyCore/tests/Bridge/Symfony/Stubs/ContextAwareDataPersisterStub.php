<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

final class ContextAwareDataPersisterStub implements ContextAwareDataPersisterInterface
{
    /**
     * @var mixed[]
     */
    private $calls = [];

    /**
     * @var bool
     */
    private $supports;

    public function __construct(?bool $supports = null)
    {
        $this->supports = $supports ?? true;
    }

    /**
     * @return mixed[]
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     *
     * @return mixed
     */
    public function persist($data, ?array $context = null)
    {
        $this->calls['persist'] = [$data, $context];

        return $data;
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function remove($data, ?array $context = null): void
    {
        $this->calls['remove'] = [$data, $context];
    }

    /**
     * @param mixed $data
     * @param null|mixed[] $context
     */
    public function supports($data, ?array $context = null): bool
    {
        $this->calls['supports'] = [$data, $context];

        return $this->supports;
    }
}
