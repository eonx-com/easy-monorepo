<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/StateProvider/PrintingHouseStateProvider.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\StateProvider;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\StateProvider;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/StateProvider/PrintingHouseStateProvider.php

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/StateProvider/PrintingHouseStateProvider.php
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource\PrintingHouse;
========
use EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource\PrintingHouse;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/StateProvider/PrintingHouseStateProvider.php
use LogicException;

final class PrintingHouseStateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            $printingHouse = new PrintingHouse('Some name');
            $printingHouse->id = 1;

            return $printingHouse;
        }

        throw new LogicException('Operation ' . $operation->getName() . ' not supported');
    }
}
