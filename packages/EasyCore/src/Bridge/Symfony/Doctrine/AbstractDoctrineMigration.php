<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\Doctrine;

use Doctrine\Migrations\AbstractMigration;
use EonX\EasyCore\Search\SearchServiceInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @deprecated We do not use Elasticsearch anymore
 */
abstract class AbstractDoctrineMigration extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param string[] $indices
     */
    protected function deleteIndices(array $indices): void
    {
        if ($this->container === null) {
            $this->warnIf(true, 'Container is null, do not delete indices');

            return;
        }

        try {
            $this->container
                ->get(SearchServiceInterface::class)
                ->deleteIndices($indices);
        } catch (\Throwable $throwable) {
            $this->warnIf(true, $throwable->getMessage());
        }
    }
}
