<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Fixture\App\StateProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use EonX\EasyErrorHandler\Tests\Fixture\App\ApiResource\PublishingHouse;
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
