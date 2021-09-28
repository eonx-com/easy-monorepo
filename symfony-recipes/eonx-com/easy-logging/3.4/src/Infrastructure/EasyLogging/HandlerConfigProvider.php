<?php
declare(strict_types=1);

namespace App\Infrastructure\EasyLogging;

use EonX\EasyLogging\Config\HandlerConfig;
use EonX\EasyLogging\Formatters\JsonFormatter;
use EonX\EasyLogging\Interfaces\Config\HandlerConfigProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class HandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        $formatter = new JsonFormatter();
        $appHandler = (new StreamHandler('php://stdout', Logger::DEBUG))->setFormatter($formatter);
        $restHandler = (new StreamHandler('php://stdout', Logger::WARNING))->setFormatter($formatter);

        yield (new HandlerConfig($appHandler))->channels(['app']);
        yield (new HandlerConfig($restHandler))->exceptChannels(['app']);
    }
}
