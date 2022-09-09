<?php

declare(strict_types=1);

namespace EonX\EasyCore\Bridge\Symfony\ApiPlatform\OpenApi\Processor;

interface DecorationProcessorInterface
{
    /**
     * @param mixed[] $documentation
     *
     * @return mixed[]
     */
    public function process(array $documentation): array;
}
