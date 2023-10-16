<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Tests\Resolvers;

use ApiPlatform\Exception\InvalidArgumentException;
use EonX\EasyErrorHandler\Resolvers\DefaultIgnoreExceptionsResolver;
use EonX\EasyErrorHandler\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Throwable;
use Exception;

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
            'exception' => new InvalidArgumentException('The type of the "foo" attribute must be "bar", "baz" given'),
            'expectedResult' => false,
        ];

        yield 'Ignored validation exception' => [
            'ignoredExceptions' => [],
            'ignoreValidationErrors' => true,
            'exception' => new InvalidArgumentException('The type of the "foo" attribute must be "bar", "baz" given'),
            'expectedResult' => true,
        ];
    }

    #[DataProvider('provideExceptions')]
    public function testShouldIgnore(
        array     $ignoredExceptions,
        bool      $ignoreValidationErrors,
        Throwable $exception,
        bool      $expectedResult
    ): void {
        $resolver = new DefaultIgnoreExceptionsResolver($ignoredExceptions, $ignoreValidationErrors);

        $result = $resolver->shouldIgnore($exception);

        self::assertSame($expectedResult, $result);
    }
}
