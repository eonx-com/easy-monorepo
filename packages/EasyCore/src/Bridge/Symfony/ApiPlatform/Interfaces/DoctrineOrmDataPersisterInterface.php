<?php
declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\Interfaces;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

interface DoctrineOrmDataPersisterInterface extends ContextAwareDataPersisterInterface
{
    // Used for DI.
}
