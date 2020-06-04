<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Providers;

use EonX\EasyDecision\Exceptions\InvalidMappingException;
use EonX\EasyDecision\Interfaces\MappingProviderInterface;

abstract class AbstractMappingProvider implements MappingProviderInterface
{
    /**
     * @var string[]
     */
    protected $typesMapping = [];

    /**
     * {@inheritdoc}
     */
    public function getDecisionType(string $name): string
    {
        if (empty($this->typesMapping[$name]) === true) {
            throw new InvalidMappingException(\sprintf('The "%s" decision type is not configured', $name));
        }

        return $this->typesMapping[$name];
    }
}
