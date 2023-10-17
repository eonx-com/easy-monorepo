<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Resolvers;

use EonX\EasyErrorHandler\Resolvers\DefaultIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;
use TypeError;

final class DefaultIgnoreExceptionsResolverTest extends AbstractTestCase
{
    public static function provideExceptions(): iterable
    {
        yield 'Not ignored exception' => [
            'ignoredExceptions' => [],
            'ignoreValidationErrors' => false,
            'exception' => new Exception(),
            'expectedResult' => false,
        ];

        yield 'Ignored exception' => [
            'ignoredExceptions' => [Exception::class],
            'ignoreValidationErrors' => false,
            'exception' => new Exception(),
            'expectedResult' => true,
        ];

        yield 'Not ignored validation exception' => [
            'ignoredExceptions' => [],
            'ignoreValidationErrors' => false,
            'exception' => new TypeError(' Argument  ($foo) must be of type A\\B, B\\C given'),
            'expectedResult' => false,
        ];

        yield 'Ignored validation exception' => [
            'ignoredExceptions' => [],
            'ignoreValidationErrors' => true,
            'exception' => new TypeError(' Argument  ($foo) must be of type A\\B, B\\C given'),
            'expectedResult' => true,
        ];
    }

    #[DataProvider('provideExceptions')]
    public function testShouldIgnore(
        array $ignoredExceptions,
        bool $ignoreValidationErrors,
        Throwable $exception,
        bool $expectedResult,
    ): void {
        $resolver = new DefaultIgnoreExceptionsResolver($ignoredExceptions, $ignoreValidationErrors);

        $result = $resolver->shouldIgnore($exception);

        self::assertSame($expectedResult, $result);
    }
}
