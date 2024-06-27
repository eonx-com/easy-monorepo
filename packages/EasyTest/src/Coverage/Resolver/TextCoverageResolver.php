<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Resolver;

use EonX\EasyTest\Coverage\Exception\UnableToResolveCoverageException;
use EonX\EasyTest\Coverage\ValueObject\CoverageReport;
use Nette\Utils\Strings;

final class TextCoverageResolver implements CoverageResolverInterface
{
    public function resolve(string $coverageOutput): CoverageReport
    {
        // Lower and remove spaces
        $output = Strings::replace(Strings::lower($coverageOutput), '/ /', '');

        if (\str_contains($output, 'lines:') === false) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output does not contain "lines:"',
                self::class
            ));
        }

        $match = Strings::match($output, '/lines:(\d+.\d+\d+)%/i') ?? [];

        if (isset($match[1])) {
            return new CoverageReport((float)$match[1]);
        }

        throw new UnableToResolveCoverageException(\sprintf(
            '[%s] Could not match any coverage number in output',
            self::class
        ));
    }
}
