<?php

declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use Closure;

interface DatabaseRepositoryInterface extends ObjectRepositoryInterface
{
    public function beginTransaction(): void;

    public function commit(): void;

    public function flush(): void;

    public function rollback(): void;

    /**
     * @return mixed
     */
    public function transactional(Closure $func);
}
