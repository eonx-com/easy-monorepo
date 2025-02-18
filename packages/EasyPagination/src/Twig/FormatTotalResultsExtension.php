<?php
declare(strict_types=1);

namespace EonX\EasyPagination\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormatTotalResultsExtension extends AbstractExtension
{
    private int $maxPreciseNumResults;

    public function __construct(?int $maxPreciseNumResults = null)
    {
        $this->maxPreciseNumResults = $maxPreciseNumResults ?? 100_000;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('formatTotalResults', $this->formatTotalResults(...)),
        ];
    }

    /**
     * @return array{numResults: string, prefix: ?string, suffix: ?string}
     */
    private function formatTotalResults(int $numResults): array
    {
        if ($numResults <= $this->maxPreciseNumResults || $numResults <= 100_000) {
            return [
                'numResults' => (string)$numResults,
                'prefix' => null,
                'suffix' => null,
            ];
        }

        $suffix = '';
        if ($numResults > 100_000 && $numResults < 1000_000) {
            $numResults = \round($numResults / 1000);
            $suffix = 'K';
        }
        if ($numResults >= 1000_000 && $numResults < 1000_000_000) {
            $numResults = \round($numResults / 1000_000, 1);
            $suffix = 'M';
        }
        if ($numResults >= 1000_000_000) {
            $numResults = \round($numResults / 1000_000_000, 2);
            $suffix = 'B';
        }

        $numResults = (string)$numResults;
        if (\str_contains($numResults, '.') === true) {
            $numResults = \rtrim($numResults, '0');
        }

        return [
            'numResults' => \rtrim($numResults, '.'),
            'prefix' => 'About',
            'suffix' => $suffix,
        ];
    }
}
