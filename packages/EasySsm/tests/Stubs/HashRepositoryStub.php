<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Stubs;

use EonX\EasySsm\Services\Hash\HashRepositoryInterface;

final class HashRepositoryStub implements HashRepositoryInterface
{
    /**
     * @var string[]
     */
    private $forGet;

    /**
     * @var string[]
     */
    private $saved = [];

    /**
     * @param null|mixed[] $forGet
     */
    public function __construct(?array $forGet = null)
    {
        $this->forGet = $forGet ?? [];
    }

    public function get(string $name): ?string
    {
        return $this->forGet[$name] ?? $this->saved[$name] ?? null;
    }

    public function save(string $name, string $hash): void
    {
        $this->saved[$name] = $hash;
    }
}
