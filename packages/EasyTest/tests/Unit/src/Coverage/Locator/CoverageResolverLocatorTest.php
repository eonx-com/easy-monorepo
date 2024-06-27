<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Unit\Coverage\Locator;

use EonX\EasyTest\Coverage\Kernel\EasyTestKernel;
use EonX\EasyTest\Coverage\Locator\CoverageResolverLocator;
use EonX\EasyTest\Coverage\Resolver\CloverCoverageResolver;
use EonX\EasyTest\Coverage\Resolver\TextCoverageResolver;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoverageResolverLocatorTest extends TestCase
{
    /**
     * @see testCreateResolverSucceeds
     */
    public static function provideSupportedFilepath(): iterable
    {
        yield [
            '/foo/bar/report.txt',
            TextCoverageResolver::class,
        ];
        yield [
            '/foo/bar/report.clover',
            CloverCoverageResolver::class,
        ];
    }

    /**
     * @param class-string $expectedResolverClass
     */
    #[DataProvider('provideSupportedFilepath')]
    public function testCreateResolverSucceeds(string $filePath, string $expectedResolverClass): void
    {
        $kernel = new EasyTestKernel('test', true);
        $kernel->boot();
        $resolverLocator = $kernel->getContainer()
            ->get(CoverageResolverLocator::class);

        $resolver = $resolverLocator->getCoverageResolver($filePath);

        self::assertInstanceOf($expectedResolverClass, $resolver);
    }

    public function testFailedIfUnsupportedReportFormat(): void
    {
        $kernel = new EasyTestKernel('test', true);
        $kernel->boot();
        $resolverLocator = $kernel->getContainer()
            ->get(CoverageResolverLocator::class);

        $this->expectException(InvalidArgumentException::class);
        $resolverLocator->getCoverageResolver('/foo/bar/report.unsupported');
    }
}
