<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Bridge\Symfony;

use EonX\EasyBatch\Bridge\Symfony\DependencyInjection\EasyBatchExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyBatchSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyBatchExtension();
    }
}
