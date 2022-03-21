<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\External\Interfaces;

interface JwtDriverInterface
{
    /**
     * @return mixed[]|object
     */
    public function decode(string $token): mixed;

    /**
     * @param mixed[]|object $input
     */
    public function encode(array|object $input): string;
}
