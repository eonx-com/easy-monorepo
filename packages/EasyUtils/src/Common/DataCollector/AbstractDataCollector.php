<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Common\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\TemplateAwareDataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\String\ByteString;

abstract class AbstractDataCollector extends DataCollector implements TemplateAwareDataCollectorInterface
{
    public static function getTemplate(): ?string
    {
        $fqcnParts = \explode('\\', static::class);
        $lastPart = \array_pop($fqcnParts);
        $collectorName = (new ByteString(\substr($lastPart, 0, -\strlen('DataCollector'))))->snake();

        return '@' . $fqcnParts[1] . '/collector/' . $collectorName . '_collector.html.twig';
    }

    public function getName(): string
    {
        return static::class;
    }
}
