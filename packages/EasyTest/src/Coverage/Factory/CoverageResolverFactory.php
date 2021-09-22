<?php

declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Factory;

use EonX\EasyTest\Coverage\Resolvers\CloverCoverageResolver;
use EonX\EasyTest\Coverage\Resolvers\TextCoverageResolver;
use EonX\EasyTest\Interfaces\CoverageResolverFactoryInterface;
use EonX\EasyTest\Interfaces\CoverageResolverInterface;
use InvalidArgumentException;

final class CoverageResolverFactory implements CoverageResolverFactoryInterface
{
    private const CLOVER_EXTENSION = 'clover';

    private const TXT_EXTENSION = 'txt';

    public function create(string $filePath): CoverageResolverInterface
    {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        switch ($ext) {
            case self::CLOVER_EXTENSION:
                $resolver = new CloverCoverageResolver();
                break;
            case self::TXT_EXTENSION:
                $resolver = new TextCoverageResolver();
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported file extension: `%s`', $ext));
        }

        return $resolver;
    }
}
