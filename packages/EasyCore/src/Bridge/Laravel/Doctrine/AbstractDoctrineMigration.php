<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Laravel\Doctrine;

use Doctrine\Migrations\AbstractMigration;
use EonX\EasyCore\Search\SearchServiceInterface;

abstract class AbstractDoctrineMigration extends AbstractMigration
{
    /**
     * @param string[] $indices
     */
    protected function deleteIndices(array $indices): void
    {
        try {
            \app(SearchServiceInterface::class)->deleteIndices($indices);
        } catch (\Throwable $throwable) {
            $this->warnIf(true, $throwable->getMessage());
        }
    }
}
