<?php

declare(strict_types=1);

namespace EonX\EasyTest\Tests\Coverage\Factory;

use EonX\EasyTest\Coverage\Factory\CoverageResolverFactory;
use EonX\EasyTest\Coverage\Resolvers\CloverCoverageResolver;
use EonX\EasyTest\Coverage\Resolvers\TextCoverageResolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CoverageResolverFactoryTest extends TestCase
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
        $factory = new CoverageResolverFactory();

        $resolver = $factory->create($filePath);

        self::assertInstanceOf($expectedResolverClass, $resolver);
    }

    public function testFailedIfUnsupportedReportFormat(): void
    {
        $factory = new CoverageResolverFactory();

        $this->expectException(InvalidArgumentException::class);
        $factory->create('/foo/bar/report.unsupported');
    }
}
