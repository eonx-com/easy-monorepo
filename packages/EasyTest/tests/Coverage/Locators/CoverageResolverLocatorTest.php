<?php

declare(strict_types=1);

namespace EonX\EasyTest\Tests\Coverage\Locators;

use EonX\EasyTest\Coverage\Locators\CoverageResolverLocator;
use EonX\EasyTest\Coverage\Resolvers\CloverCoverageResolver;
use EonX\EasyTest\Coverage\Resolvers\TextCoverageResolver;
use EonX\EasyTest\HttpKernel\EasyTestKernel;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CoverageResolverLocatorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function provideSupportedFilepath(): array
    {
        return [
            [
                '/foo/bar/report.txt',
                TextCoverageResolver::class,
            ],
            [
                '/foo/bar/report.clover',
                CloverCoverageResolver::class,
            ],
        ];
    }

    /**
     * @param class-string $expectedResolverClass
     *
     * @dataProvider provideSupportedFilepath
     */
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
