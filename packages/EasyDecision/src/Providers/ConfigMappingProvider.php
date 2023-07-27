<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Providers;

final class ConfigMappingProvider extends AbstractMappingProvider
{
    /**
     * @param string[] $decisionsConfig
     */
    public function __construct(array $decisionsConfig)
    {
        $this->setTypesMapping($decisionsConfig);
    }
}
