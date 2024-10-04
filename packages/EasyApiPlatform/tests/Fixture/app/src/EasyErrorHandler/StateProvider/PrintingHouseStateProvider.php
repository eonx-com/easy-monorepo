<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\StateProvider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use EonX\EasyApiPlatform\Tests\Fixture\App\EasyErrorHandler\ApiResource\PrintingHouse;
use LogicException;

final class PrintingHouseStateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Get) {
            if ($uriVariables['id'] === 1) {
                $printingHouse = new PrintingHouse('Some name');
                $printingHouse->id = 1;

                return $printingHouse;
            }

            return null;
        }

        throw new LogicException('Operation ' . $operation->getName() . ' not supported');
    }
}
