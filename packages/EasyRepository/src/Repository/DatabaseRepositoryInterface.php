<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Repository;

use Closure;

interface DatabaseRepositoryInterface extends ObjectRepositoryInterface
{
    public function beginTransaction(): void;

    public function commit(): void;

    public function flush(): void;

    public function rollback(): void;

    public function transactional(Closure $func): mixed;
}
