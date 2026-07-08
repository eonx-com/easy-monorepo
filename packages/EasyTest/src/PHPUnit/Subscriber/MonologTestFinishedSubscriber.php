<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Subscriber;

use EonX\EasyTest\Monolog\Processor\LogCollectorProcessor;
use Monolog\LogRecord;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;

final class MonologTestFinishedSubscriber implements FinishedSubscriber
{
    public function notify(Finished $event): void
    {
        if (\class_exists(LogRecord::class) === false) {
            return;
        }

        LogCollectorProcessor::reset();
    }
}
