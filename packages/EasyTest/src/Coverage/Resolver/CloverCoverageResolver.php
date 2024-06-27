<?php
declare(strict_types=1);

namespace EonX\EasyTest\Coverage\Resolver;

use EonX\EasyTest\Coverage\Exception\UnableToResolveCoverageException;
use EonX\EasyTest\Coverage\ValueObject\CoverageReport;
use Exception;
use SimpleXMLElement;

final class CloverCoverageResolver implements CoverageResolverInterface
{
    private const ATTRIBUTE_NAME_COVERED_ELEMENTS = 'coveredelements';

    private const ATTRIBUTE_NAME_ELEMENTS = 'elements';

    private const ATTRIBUTE_NAME_FILE_NAME = 'name';

    public function resolve(string $coverageOutput): CoverageReport
    {
        $violations = [];

        try {
            $xml = new SimpleXMLElement($coverageOutput);
        } catch (Exception) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output could not be parsed',
                self::class
            ));
        }

        $metrics = $xml->xpath('//file/metrics');

        foreach ($metrics as $metric) {
            $elements = (int)$this->extractXmlAttribute($metric, self::ATTRIBUTE_NAME_ELEMENTS);
            $coveredElements = (int)$this->extractXmlAttribute($metric, self::ATTRIBUTE_NAME_COVERED_ELEMENTS);

            if ($elements !== $coveredElements) {
                $file = $metric->xpath('parent::*')[0];
                $violations[] = $this->extractXmlAttribute($file, self::ATTRIBUTE_NAME_FILE_NAME);
            }
        }

        $totalElements = (int)$xml->xpath('//project/metrics/@elements')[0];
        $totalCoveredElements = (int)$xml->xpath('//project/metrics/@coveredelements')[0];

        $coverage = $totalElements === 0 ? 100 : ($totalCoveredElements / $totalElements) * 100;

        return new CoverageReport($coverage, $violations);
    }

    private function extractXmlAttribute(SimpleXMLElement $element, string $attributeName): string
    {
        $attr = $element->attributes();

        if ($attr === null) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output could not be parsed',
                self::class
            ));
        }

        if (isset($attr[$attributeName]) === false) {
            throw new UnableToResolveCoverageException(\sprintf(
                '[%s] Given output could not be parsed',
                self::class
            ));
        }

        return (string)$attr[$attributeName];
    }
}
