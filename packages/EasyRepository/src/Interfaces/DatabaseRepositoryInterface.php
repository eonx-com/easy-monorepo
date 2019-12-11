<?php
declare(strict_types=1);

namespace EonX\EasyRepository\Interfaces;

use Closure;

interface DatabaseRepositoryInterface extends ObjectRepositoryInterface
{
    /**
     * Starts a transaction on the underlying database connection.
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commits a transaction on the underlying database connection.
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Synchronise in-memory changes to database.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Performs a rollback on the underlying database connection.
     *
     * @return void
     */
    public function rollback(): void;

    /**
     * Executes a function in a transaction.
     * If an exception occurs during execution of the function or flushing or transaction commit,
     * the transaction is rolled back, the EntityManager closed and the exception re-thrown.
     *
     * @param \Closure $func
     *
     * @return mixed
     */
    public function transactional(Closure $func);
}
