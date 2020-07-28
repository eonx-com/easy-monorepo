<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces;

interface ApiTokenDecoderProviderInterface
{
    /**
     * @return iterable<\EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface>
     */
    public function getDecoders(): iterable;
}
