<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Hash;

interface HashRepositoryInterface
{
    public function get(string $name): ?string;

    public function save(string $name, string $hash): void;
}
