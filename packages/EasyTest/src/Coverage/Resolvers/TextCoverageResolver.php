<?php

declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Resolvers;

use EonX\EasyTest\Exceptions\UnableToResolveCoverageException;
use EonX\EasyTest\Interfaces\CoverageResolverInterface;
use Nette\Utils\Strings;

final class TextCoverageResolver implements CoverageResolverInterface
{
    public function resolve(string $coverageOutput): float
    {
        // Lower and remove spaces
        $output = Strings::replace(Strings::lower($coverageOutput), '/ /', '');

        if (Strings::contains($output, 'lines:') === false) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output does not contain "lines:"',
                static::class
            ));
        }

        $match = Strings::match($output, '/lines:(\d+.\d+\d+)%/i') ?? [];

        if (isset($match[1])) {
            return (float)$match[1];
        }

        throw new UnableToResolveCoverageException(\sprintf(
            '[%s] Could not match any coverage number in output',
            static::class
        ));
    }
}
