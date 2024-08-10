<?php
declare(strict_types=1);

namespace EonX\EasyTest\PHPUnit\Subscriber;

use EonX\EasyTest\EasyErrorHandler\Common\ErrorHandler\TraceableErrorHandlerStub;
use EonX\EasyTest\HttpClient\Factory\TestResponseFactory;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Exception as BasePhpUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Color;

final class HttpClientTestFailedSubscriber implements FailedSubscriber
{
    public function notify(Failed $event): void
    {
        if (\count(TraceableErrorHandlerStub::getAllReportedErrors()) > 0) {
            echo \PHP_EOL;
            echo Color::colorize('fg-red', 'Error handler reported the following exceptions:');

            foreach (TraceableErrorHandlerStub::getAllReportedErrors() as $throwable) {
                echo \PHP_EOL;
                echo '- ' . $throwable->getFile() . ':' . $throwable->getLine() . \PHP_EOL;
                echo '  code: ' . $throwable->getCode() . \PHP_EOL;
                echo '  message: ' . $throwable->getMessage() . \PHP_EOL;

                if ($throwable instanceof ExpectationFailedException) {
                    echo '  comparison failure: ' . \PHP_EOL;
                    echo '    diff: ' . $throwable->getComparisonFailure()?->getDiff() . \PHP_EOL;
                    echo '    expected: ' . $throwable->getComparisonFailure()?->getExpectedAsString() . \PHP_EOL;
                    echo '    actual: ' . $throwable->getComparisonFailure()?->getActualAsString() . \PHP_EOL;
                }
            }
        }

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
