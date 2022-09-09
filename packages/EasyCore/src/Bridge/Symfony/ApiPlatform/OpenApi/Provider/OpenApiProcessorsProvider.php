<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Provider;

use EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor\DefaultDecorationProcessorInterface;

class OpenApiProcessorsProvider
{
    /**
     * @param bool $defaultProcessorsEnabled
     * @param string[] $processors
     */
    public function __construct(
        private bool $defaultProcessorsEnabled,
        private iterable $processors
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function provide(): array
    {
        $processors = [];
        foreach ($this->processors as $processor) {
            if ($this->defaultProcessorsEnabled === false &&
                \is_a($processor, DefaultDecorationProcessorInterface::class, true) === true) {
                continue;
            }
            $processors[] = $processor;
        }

        return $processors;
    }
}
