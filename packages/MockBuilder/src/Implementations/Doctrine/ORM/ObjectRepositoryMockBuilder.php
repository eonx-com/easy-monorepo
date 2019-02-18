<?php
declare(strict_types=1);

namespace StepTheFkUp\MockBuilder\Implementations\Doctrine\ORM;

use Doctrine\Common\Persistence\ObjectRepository;
use StepTheFkUp\MockBuilder\AbstractMockBuilder;

/**
 * @method self hasFind($id, null|int $lockMode = null, null|int $lockVersion = null)
 * @method self hasFindAll()
 * @method self hasFindBy(array $criteria, ?array $orderBy = null, null|int $limit = null, null|int $offset = null)
 * @method self hasFindOneBy(array $criteria, ?array $orderBy = null)
 * @method self hasGetClassName()
 *
 * @see \Doctrine\Common\Persistence\ObjectRepository
 */
final class ObjectRepositoryMockBuilder extends AbstractMockBuilder
{
    /**
     * Get class to mock.
     *
     * @return string
     */
    protected function getClassToMock(): string
    {
        return ObjectRepository::class;
    }
}
