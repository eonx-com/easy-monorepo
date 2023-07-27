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
    protected array $typesMapping = [];

    public function getDecisionType(string $name): string
    {
        if (($this->typesMapping[$name] ?? '') === '') {
            throw new InvalidMappingException(\sprintf('Decision for name "%s" is not configured', $name));
        }

        if (\class_exists($this->typesMapping[$name]) === false) {
            throw new InvalidMappingException(
                \sprintf(
                    'Decision class "%s" for name "%s" is not a valid classname',
                    $this->typesMapping[$name],
                    $name
                )
            );
        }

        return $this->typesMapping[$name];
    }

    /**
     * @param string[] $decisionsConfig
     */
    protected function setTypesMapping(array $decisionsConfig): void
    {
        foreach ($decisionsConfig as $name => $config) {
            if (\is_string($name) && \is_string($config)) {
                $this->typesMapping[$name] = $config;
            }
        }
    }
}
