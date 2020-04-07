<?php

declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\DataPersister\AbstractContextDataPersister;

final class ContextDataPersisterStub extends AbstractContextDataPersister
{
    protected function getApiResourceClass(): string
    {
        return EntityStub::class;
    }
}
