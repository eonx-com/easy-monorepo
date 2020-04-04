<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractDataPersister;

final class DataPersisterStub extends AbstractDataPersister
{
    /**
     * @inheritDoc
     */
    protected function getApiResourceClass(): string
    {
        return EntityStub::class;
    }
}
