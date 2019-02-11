<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyRepository\Interfaces;

interface ObjectRepositoryInterface
{
    /**
     * Get all the objects managed by the repository.
     *
     * @return object[]
     */
    public function all(): array;

    /**
     * Delete given object(s).
     *
     * @param object|object[] $object
     *
     * @return void
     */
    public function delete($object): void;

    /**
     * Find object for given identifier, return null if not found.
     *
     * @param int|string $identifier
     *
     * @return null|object
     */
    public function find($identifier);

    /**
     * Save given object(s).
     *
     * @param object|object[] $object The object or list of objects to save
     *
     * @return void
     */
    public function save($object): void;
}
