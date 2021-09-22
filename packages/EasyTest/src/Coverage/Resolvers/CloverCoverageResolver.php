<?php

declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Resolvers;

use EonX\EasyTest\Coverage\DataTransferObject\CoverageReportDto;
use EonX\EasyTest\Exceptions\UnableToResolveCoverageException;
use EonX\EasyTest\Interfaces\CoverageResolverInterface;
use Exception;
use SimpleXMLElement;

final class CloverCoverageResolver implements CoverageResolverInterface
{
    public function resolve(string $coverageOutput): CoverageReportDto
    {
        $violations = [];

        try {
            $xml = new SimpleXMLElement($coverageOutput);
        } catch (Exception $e) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output could not be parsed',
                static::class
            ));
        }

        $metrics = $xml->xpath('//file/metrics');

        foreach ($metrics as $metric) {
            $elements = (int)$metric->attributes()
                ->elements;
            $coveredElements = (int)$metric->attributes()
                ->coveredelements;

            if ($elements !== $coveredElements) {
                $file = $metric->xpath('parent::*')[0];
                $violations[] = (string)$file->attributes()->name;
            }
        }

        $totalElements = (int)$xml->xpath('//project/metrics/@elements')[0];
        $totalCoveredElements = (int)$xml->xpath('//project/metrics/@coveredelements')[0];
        $coverage = ($totalCoveredElements / $totalElements) * 100;

        return new CoverageReportDto($coverage, $violations);
    }
}
