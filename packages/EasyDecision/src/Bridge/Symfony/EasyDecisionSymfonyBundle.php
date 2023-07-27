<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Bridge\Symfony;

use EonX\EasyDecision\Bridge\Symfony\DependencyInjection\EasyDecisionExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyDecisionSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyDecisionExtension();
    }
}
