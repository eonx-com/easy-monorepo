<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Bridge\Doctrine\Coroutine\PDO;

use OpenSwoole\Core\Coroutine\Client\ClientConfigInterface;
use OpenSwoole\Core\Coroutine\Client\ClientFactoryInterface;

final class PDOClientFactory implements ClientFactoryInterface
{
    public static function make(ClientConfigInterface $config): PDOClient
    {
        return new PDOClient($config);
    }
}
