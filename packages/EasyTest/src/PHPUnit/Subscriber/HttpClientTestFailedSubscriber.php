<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Subscriber;

use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Exception as BasePhpUnitException;
use PHPUnit\Util\Color;

final class HttpClientTestFailedSubscriber implements FailedSubscriber
{
    public function notify(Failed $event): void
    {
        if (
            TestResponseFactory::getException() !== null
            && TestResponseFactory::getException() instanceof BasePhpUnitException === false
        ) {
            echo \PHP_EOL;
            echo Color::colorize('fg-red', 'HTTP client reported the following exception:');

            throw TestResponseFactory::getException();
        }
    }
}
