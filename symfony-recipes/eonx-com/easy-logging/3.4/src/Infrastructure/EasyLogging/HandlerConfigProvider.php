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
     * @var string
     */
    private const APP_CHANNEL = 'app';

    /**
     * @var string
     */
    private const STREAM = 'php://stdout';

    /**
     * @return iterable<\EonX\EasyLogging\Interfaces\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        $formatter = new JsonFormatter();
        $appHandler = (new StreamHandler(self::STREAM, Logger::DEBUG))->setFormatter($formatter);
        $restHandler = (new StreamHandler(self::STREAM, Logger::WARNING))->setFormatter($formatter);

        yield (new HandlerConfig($appHandler))->channels([self::APP_CHANNEL]);
        yield (new HandlerConfig($restHandler))->exceptChannels([self::APP_CHANNEL]);
    }
}
