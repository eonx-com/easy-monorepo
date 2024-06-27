<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Doctrine\Factory;

use EonX\EasySwoole\Doctrine\Client\PdoClient;
use OpenSwoole\Core\Coroutine\Client\ClientConfigInterface;
use OpenSwoole\Core\Coroutine\Client\ClientFactoryInterface;

final class PdoClientFactory implements ClientFactoryInterface
{
    public static function make(ClientConfigInterface $config): PdoClient
    {
        return new PdoClient($config);
    }
}
