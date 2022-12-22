<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Interfaces\AsyncMessageInterface;
use EonX\EasyBatch\Tests\Bridge\Symfony\Fixtures\App\Interfaces\SyncMessageInterface;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $frameworkConfig): void {
    $messenger = $frameworkConfig->messenger();

    $messenger->resetOnMessage(true);

    $messenger->bus('messenger.bus.default')
        ->defaultMiddleware(true);

    $messenger->failureTransport('failed');

    $messenger->transport('sync')
        ->dsn('sync://');

    $messenger->transport('failed')
        ->dsn('sync://');

    $messenger->transport('async')
        ->dsn('in-memory://');

    $messenger->routing(AsyncMessageInterface::class)
        ->senders(['async']);

    $messenger->routing(SyncMessageInterface::class)
        ->senders(['sync']);
};
