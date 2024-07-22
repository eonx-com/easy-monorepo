<?php
declare(strict_types=1);

<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/StateProvider/PublishingHouseStateProvider.php
namespace EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\StateProvider;
========
namespace EonX\EasyErrorHandler\Tests\Fixture\App\StateProvider;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/StateProvider/PublishingHouseStateProvider.php

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
<<<<<<<< HEAD:packages/EasyApiPlatform/tests/Fixtures/app/src/Case/EasyErrorHandler/StateProvider/PublishingHouseStateProvider.php
use EonX\EasyApiPlatform\Tests\Fixtures\App\Case\EasyErrorHandler\ApiResource\PublishingHouse;
========
use EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource\PublishingHouse;
>>>>>>>> refs/heads/6.x:packages/EasyErrorHandler/tests/Fixture/app/src/StateProvider/PublishingHouseStateProvider.php
use LogicException;

final class PublishingHouseStateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            $publishingHouse = new PublishingHouse('Some name');
            $publishingHouse->id = 1;

            return $publishingHouse;
        }

        throw new LogicException('Operation ' . $operation->getName() . ' not supported');
    }
}
