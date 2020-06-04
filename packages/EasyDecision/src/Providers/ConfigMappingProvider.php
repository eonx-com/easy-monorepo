<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Providers;

final class ConfigMappingProvider extends AbstractMappingProvider
{
    /**
     * ConfigMappingProvider constructor.
     *
     * @param string[] $decisionsConfig
     */
    public function __construct(array $decisionsConfig)
    {
        $this->setTypesMapping($decisionsConfig);
    }

    /**
     * Sets types mapping.
     *
     * @param string[] $decisionsConfig
     */
    private function setTypesMapping(array $decisionsConfig): void
    {
        foreach ($decisionsConfig as $name => $config) {
            if (\is_string($name) && \is_string($config)) {
                $this->typesMapping[$name] = $config;
            }
        }
    }
}
