<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Bridge\Symfony;

use EonX\EasyPagination\Bridge\Symfony\DependencyInjection\EasyPaginationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class EasyPaginationSymfonyBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new EasyPaginationExtension();
    }
}
